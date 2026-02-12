<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    public function blog(): View
    {
        return $this->placeholder(
            'Blog',
            'Blog page placeholder',
            'Long-form articles and product updates will be published here next.'
        );
    }

    public function services(): View
    {
        return $this->placeholder(
            'Services',
            'Services landing',
            'This landing page will introduce all Arosoft service lines and case studies.'
        );
    }

    public function printing(): View
    {
        return $this->placeholder(
            'Printing',
            'Printing services',
            'Detailed printing packages, turnaround windows, and portfolio highlights will appear here.'
        );
    }

    public function websiteDesign(): View
    {
        return $this->placeholder(
            'Website Design',
            'Website design services',
            'Design process, UX strategy, and sample website showcases are coming soon.'
        );
    }

    public function webDevelopment(): View
    {
        return $this->placeholder(
            'Web Development',
            'Web development services',
            'Engineering capabilities, frameworks, and delivery process details will be listed here.'
        );
    }

    public function trainingCourses(): View
    {
        return $this->placeholder(
            'Training/Courses',
            'Training and courses',
            'Upcoming technical training tracks and enrollment details will be available on this page.'
        );
    }

    public function tutorials(): View
    {
        return $this->placeholder(
            'Tutorials',
            'Tutorials',
            'Practical tutorials, walkthroughs, and implementation guides will be published here.'
        );
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function tools(): View
    {
        return view('pages.tools');
    }

    private function placeholder(string $title, string $heading, string $copy): View
    {
        return view('pages.placeholder', [
            'title' => $title,
            'heading' => $heading,
            'copy' => $copy,
        ]);
    }
}
