<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isRoot()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // The ticket's creator can always view their own ticket.
        if ($user->id === $ticket->created_by) {
            return true;
        }

        // For other users (Admins/Viewers), they must be authorized by department/assignment...
        $isAuthorizedByRole = ($ticket->category?->mainDepartment?->users()->where('users.id', $user->id)->exists())
            || $ticket->assignedUsers->contains($user->id);

        // ...and they must also pass the branch check.
        return $isAuthorizedByRole && $this->shareBranch($user, $ticket);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Admins/Viewers must be authorized by department/assignment...
        $isAuthorizedByRole = ($ticket->category?->department?->users()->where('users.id', $user->id)->exists())
            || $ticket->assignedUsers->contains($user->id);

        // ...and they must also pass the branch check.
        return $isAuthorizedByRole && $this->shareBranch($user, $ticket);
    }

    /**
     * Check if the user shares at least one branch with the ticket creator.
     * - If the user (admin/viewer) has no branches assigned, they are considered a "global" user
     *   and can see tickets from all branches. This is a common case for IT staff.
     * - If the user has branches, the ticket creator must be in one of those branches.
     */
    private function shareBranch(User $user, Ticket $ticket): bool
    {
        $userBranchIds = $user->branches()->pluck('branches.id');

        // If the viewing user (e.g., an admin) has no branches assigned, they are not restricted.
        // This allows IT or global admins (whose OUs might not map to a branch) to see all tickets.
        if ($userBranchIds->isEmpty()) {
            return true;
        }

        // If the admin is assigned to specific branches, the ticket creator must belong to one of them.
        return $ticket->creator && $ticket->creator->branches()->whereIn('branches.id', $userBranchIds)->exists();
    }


}
