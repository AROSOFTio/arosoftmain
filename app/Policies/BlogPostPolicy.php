<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use App\Support\AdminSettings;

class BlogPostPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $this->canManageBlog($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, BlogPost $blogPost): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, BlogPost $blogPost): bool
    {
        return false;
    }

    public function delete(User $user, BlogPost $blogPost): bool
    {
        return false;
    }

    public function restore(User $user, BlogPost $blogPost): bool
    {
        return false;
    }

    public function forceDelete(User $user, BlogPost $blogPost): bool
    {
        return false;
    }

    private function canManageBlog(User $user): bool
    {
        if ($user->is_admin) {
            return true;
        }

        $emails = array_map(
            static fn (string $email): string => strtolower(trim($email)),
            AdminSettings::get('admin_emails', config('blog.admin_emails', []))
        );

        return in_array(strtolower((string) $user->email), $emails, true);
    }
}
