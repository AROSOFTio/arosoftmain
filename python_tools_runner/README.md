# Python Tools Runner

Use this service when PHP shell functions (`exec`, `shell_exec`, `proc_open`) are disabled.
Laravel remains the main website; this runner only executes heavy tool commands (yt-dlp/ffmpeg).

## What it provides

- `POST /youtube/formats`
- `POST /youtube/download`
- `GET /health`

## Server prerequisites

- Python 3.10+
- `yt-dlp` installed in PATH
- `ffmpeg` installed in PATH (for MP3)

## Setup

```bash
cd /www/wwwroot/arosoft.io/python_tools_runner
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

## Run

```bash
export TOOLS_RUNNER_TOKEN="change-me-strong-token"
export TOOLS_RUNNER_WORKDIR="/tmp/arosoft-tools"
uvicorn main:app --host 127.0.0.1 --port 8099
```

## Laravel `.env` values

```env
TOOLS_RUNNER_BASE_URL=http://127.0.0.1:8099
TOOLS_RUNNER_TOKEN=change-me-strong-token
TOOLS_RUNNER_TIMEOUT=300
```

Then clear config cache:

```bash
/www/server/php/82/bin/php artisan optimize:clear
/www/server/php/82/bin/php artisan config:cache
```
