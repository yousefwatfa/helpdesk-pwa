<?php

namespace App\Http\Traits;

use App\Models\Ticket;
use App\Models\User;

trait FilterAdminsByBranch
{
    /**
     * Get department admins filtered by branch.
     * Only returns admins who share at least one branch with the ticket creator.
     * If the ticket creator has no branches assigned, any admin of that department
     * will be included.
     *
     * This helper remains for backward compatibility but the preferred method is
     * `getUsersAllowedToView()` which mirrors the full TicketPolicy logic.
     */
    private function getDepartmentAdmins(Ticket $ticket): \Illuminate\Database\Eloquent\Collection
    {
        $creatorBranchIds = $ticket->creator->branches()->pluck('branches.id')->toArray();
        
        $query = User::whereHas('departments', function ($q) use ($ticket) {
            $q->where('departments.id', $ticket->category->department_id);
        })->whereHas('role', function ($q) {
            $q->where('name', 'Admin');
        });

        // If creator has no branches assigned (global user), return all dept admins
        if (empty($creatorBranchIds)) {
            return $query->get();
        }

        // Filter admins: only those who share a branch with creator.
        // Admins with no branch assignment are treated as "not eligible" when
        // the creator has one or more branches (they are not global viewers).
        return $query->get()->filter(function ($admin) use ($creatorBranchIds) {
            $adminBranchIds = $admin->branches()->pluck('branches.id')->toArray();

            // exclude admins without any branch when creator is scoped
            if (empty($adminBranchIds)) {
                return false;
            }

            return !empty(array_intersect($creatorBranchIds, $adminBranchIds));
        });
    }

    /**
     * Return all users who would be allowed to *view* the given ticket according to
     * the TicketPolicy (excluding the ticket creator).
     * This naturally applies department/assignment and branch rules exactly like
     * what the UI uses when showing tickets.
     */
    private function getUsersAllowedToView(Ticket $ticket)
    {
        $policy = new \App\Policies\TicketPolicy();
        // fetch all admins and viewers since they are the only ones that ever get
        // notifications. Filtering later will enforce the policy fully.
        $candidates = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Admin', 'Viewer']);
        })->get();

        return $candidates->filter(function ($user) use ($policy, $ticket) {
            if ($user->id === $ticket->created_by) {
                return false;
            }
            return $policy->view($user, $ticket);
        });
    }
}
