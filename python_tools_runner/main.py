from __future__ import annotations

import json
import os
import re
import shutil
import subprocess
import uuid
from pathlib import Path
from typing import Optional
from urllib.parse import parse_qs, urlparse

from fastapi import FastAPI, Form, Header, HTTPException
from fastapi.responses import JSONResponse, FileResponse
from starlette.background import BackgroundTask

app = FastAPI(title="Arosoft Tools Runner", version="1.0.0")

RUNNER_TOKEN = os.getenv("TOOLS_RUNNER_TOKEN", "").strip()
WORK_DIR = Path(os.getenv("TOOLS_RUNNER_WORKDIR", "/tmp/arosoft-tools")).resolve()
WORK_DIR.mkdir(parents=True, exist_ok=True)

ALLOWED_VIDEO_HEIGHTS = {4320, 2160, 1440, 1080, 720, 480, 360, 240, 144}
ALLOWED_AUDIO_BITRATES = {320, 256, 192, 128}


def require_token(header_token: Optional[str]) -> None:
    if RUNNER_TOKEN and header_token != RUNNER_TOKEN:
        raise HTTPException(status_code=401, detail="Unauthorized runner token.")


def parse_youtube_video(url: str) -> tuple[str, str]:
    candidate = (url or "").strip()
    if not candidate:
        raise HTTPException(status_code=422, detail="YouTube URL is required.")

    if not candidate.lower().startswith(("http://", "https://")):
        candidate = f"https://{candidate}"

    parts = urlparse(candidate)
    host = (parts.hostname or "").lower()
    path = (parts.path or "").strip("/")

    if not any(key in host for key in ("youtube.com", "youtu.be", "youtube-nocookie.com")):
        raise HTTPException(status_code=422, detail="Invalid YouTube host.")

    video_id: Optional[str] = None
    if "youtu.be" in host:
        video_id = path.split("/")[0] if path else None
    elif path == "watch":
        video_id = parse_qs(parts.query).get("v", [None])[0]
    elif path.startswith("shorts/"):
        segments = path.split("/")
        video_id = segments[1] if len(segments) > 1 else None
    elif path.startswith("embed/"):
        segments = path.split("/")
        video_id = segments[1] if len(segments) > 1 else None
    elif path.startswith("live/"):
        segments = path.split("/")
        video_id = segments[1] if len(segments) > 1 else None

    if not isinstance(video_id, str) or not re.fullmatch(r"[A-Za-z0-9_-]{11}", video_id):
        raise HTTPException(status_code=422, detail="Invalid YouTube video ID.")

    canonical = f"https://www.youtube.com/watch?v={video_id}"
    return video_id, canonical


def run_command(args: list[str]) -> tuple[int, str]:
    process = subprocess.run(args, capture_output=True, text=True)
    output = (process.stdout or "").strip()
    stderr = (process.stderr or "").strip()
    if stderr:
        output = f"{output}\n{stderr}".strip()
    return process.returncode, output


def extract_json_payload(raw: str) -> Optional[str]:
    start = raw.find("{")
    end = raw.rfind("}")
    if start == -1 or end == -1 or end <= start:
        return None
    return raw[start : end + 1]


def resolve_downloaded_file(job_token: str) -> Optional[Path]:
    matches = sorted(WORK_DIR.glob(f"{job_token}.*"), key=lambda p: p.stat().st_mtime, reverse=True)
    for path in matches:
        suffix = path.suffix.lower()
        if suffix in {".part", ".temp", ".ytdl"}:
            continue
        if path.is_file() and path.stat().st_size > 0:
            return path
    return None


def remove_file(path: Path) -> None:
    try:
        if path.exists():
            path.unlink()
    except OSError:
        pass


def format_download_error(raw: str) -> str:
    normalized = raw.lower()
    if "ffmpeg" in normalized:
        return "ffmpeg is required for merge/audio conversion on Python runner."
    if "private video" in normalized or "sign in to confirm" in normalized:
        return "This YouTube video is private or restricted."
    if "copyright" in normalized or "unavailable" in normalized:
        return "This media is unavailable for download."
    return "Python runner failed to process the YouTube request."


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}


@app.post("/youtube/formats")
def youtube_formats(
    url: str = Form(...),
    x_tools_runner_token: Optional[str] = Header(default=None),
):
    require_token(x_tools_runner_token)
    _, canonical = parse_youtube_video(url)

    yt_dlp = shutil.which("yt-dlp")
    if not yt_dlp:
        return JSONResponse(
            status_code=500,
            content={"ok": False, "message": "yt-dlp is not installed on Python runner."},
        )

    exit_code, output = run_command([yt_dlp, "--dump-single-json", "--no-playlist", "--no-warnings", canonical])
    payload = extract_json_payload(output)
    if exit_code != 0 or payload is None:
        return JSONResponse(
            status_code=422,
            content={"ok": False, "message": format_download_error(output)},
        )

    try:
        decoded = json.loads(payload)
    except json.JSONDecodeError:
        return JSONResponse(
            status_code=422,
            content={"ok": False, "message": "Unable to parse yt-dlp JSON output."},
        )

    heights = set()
    for fmt in decoded.get("formats", []):
        if not isinstance(fmt, dict):
            continue
        if str(fmt.get("vcodec", "none")) == "none":
            continue
        height = int(fmt.get("height") or 0)
        if height > 0:
            heights.add(height)

    video_heights = sorted(heights, reverse=True)
    if not video_heights:
        video_heights = [1080, 720, 480, 360]

    return {
        "ok": True,
        "title": str(decoded.get("title") or "YouTube Video"),
        "duration": int(decoded.get("duration") or 0),
        "thumbnail": str(decoded.get("thumbnail") or ""),
        "video_heights": video_heights,
    }


@app.post("/youtube/download")
def youtube_download(
    url: str = Form(...),
    type: str = Form(...),
    quality: int = Form(...),
    x_tools_runner_token: Optional[str] = Header(default=None),
):
    require_token(x_tools_runner_token)
    video_id, canonical = parse_youtube_video(url)

    if type not in {"video", "audio"}:
        return JSONResponse(status_code=422, content={"ok": False, "message": "Invalid download type."})

    yt_dlp = shutil.which("yt-dlp")
    if not yt_dlp:
        return JSONResponse(status_code=500, content={"ok": False, "message": "yt-dlp is not installed on Python runner."})

    job_token = str(uuid.uuid4())
    output_template = str(WORK_DIR / f"{job_token}.%(ext)s")

    if type == "video":
        if quality not in ALLOWED_VIDEO_HEIGHTS:
            return JSONResponse(status_code=422, content={"ok": False, "message": "Unsupported video quality requested."})

        label = f"{quality}p"
        selector = f"bestvideo[height<={quality}]+bestaudio/best[height<={quality}]"
        args = [
            yt_dlp,
            "--no-playlist",
            "--no-warnings",
            "--restrict-filenames",
            "--force-overwrites",
            "--merge-output-format",
            "mp4",
            "-f",
            selector,
            "-o",
            output_template,
            canonical,
        ]
    else:
        if quality not in ALLOWED_AUDIO_BITRATES:
            return JSONResponse(status_code=422, content={"ok": False, "message": "Unsupported MP3 quality requested."})

        if not shutil.which("ffmpeg"):
            return JSONResponse(status_code=500, content={"ok": False, "message": "ffmpeg is required for MP3 conversion."})

        label = f"mp3-{quality}kbps"
        args = [
            yt_dlp,
            "--no-playlist",
            "--no-warnings",
            "--restrict-filenames",
            "--force-overwrites",
            "-x",
            "--audio-format",
            "mp3",
            "--audio-quality",
            "0",
            "--postprocessor-args",
            f"ffmpeg:-b:a {quality}k",
            "-o",
            output_template,
            canonical,
        ]

    exit_code, output = run_command(args)
    media_path = resolve_downloaded_file(job_token)
    if exit_code != 0 or media_path is None:
        return JSONResponse(status_code=422, content={"ok": False, "message": format_download_error(output)})

    extension = media_path.suffix.lstrip(".") or ("mp3" if type == "audio" else "mp4")
    media_type = "audio/mpeg" if type == "audio" else "video/mp4"
    filename = f"youtube-{video_id}-{label}.{extension}"

    return FileResponse(
        path=str(media_path),
        filename=filename,
        media_type=media_type,
        background=BackgroundTask(remove_file, media_path),
    )
