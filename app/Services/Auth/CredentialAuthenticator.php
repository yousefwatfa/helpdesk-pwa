<?php

namespace App\Services\Auth;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Checks a username/password: local accounts (auth_type "normal") against the
 * stored hash, and, only when LDAP is enabled in config/helpdesk.php, everyone
 * else against Active Directory. Successful LDAP logins create or refresh the
 * user and sync branches whose names match the user's AD organisational units.
 */
class CredentialAuthenticator
{
    /**
     * @return array{user: User, branch_name: ?string}|null  null when the credentials are wrong
     */
    public function attempt(string $username, string $password): ?array
    {
        $user = User::where('username', $username)->first();

        if ($user && $user->auth_type === 'normal') {
            return $user->password && Hash::check($password, $user->password)
                ? ['user' => $user, 'branch_name' => null]
                : null;
        }

        if (!config('helpdesk.ldap.enabled')) {
            return null;
        }

        return $this->ldapAttempt($username, $password);
    }

    private function ldapAttempt(string $username, string $password): ?array
    {
        if (!extension_loaded('ldap')) {
            Log::error('LDAP sign-in is enabled but the PHP ldap extension is not loaded.');
            return null;
        }

        // an empty password would be an anonymous bind, which AD accepts
        if ($password === '') {
            return null;
        }

        $config = config('helpdesk.ldap');

        $ldap_conn = ldap_connect($config['host'], $config['port']);
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        // AD style: username@domain
        $ldap_user = $username . '@' . $config['domain'];

        try {
            $bind = @ldap_bind($ldap_conn, $ldap_user, $password);
        } catch (\Exception $e) {
            $bind = false;
        }

        if (!$bind) {
            return null;
        }

        $user = User::where('username', $username)->first();

        // Search LDAP for user details
        $filter = '(sAMAccountName=' . ldap_escape($username, '', LDAP_ESCAPE_FILTER) . ')';
        $result = ldap_search($ldap_conn, $config['base_dn'], $filter);
        $entries = ldap_get_entries($ldap_conn, $result);

        // Extract the first OU (most specific) from the DN
        $firstOU = null;
        $allOUs = [];
        $dn = '';
        if ($entries['count'] > 0) {
            $dn = $entries[0]['dn'] ?? '';
            if (preg_match_all('/OU=([^,]+)/i', $dn, $matches)) {
                $allOUs = $matches[1];
                $firstOU = $allOUs[0];
            }
        }

        // users under the protected DN keep what an admin set for them
        $protectedDn = strtoupper(trim((string) $config['protected_dn']));
        $isProtectedUser = $protectedDn !== '' && str_contains(strtoupper($dn), $protectedDn);

        if (!$user) {
            if ($entries['count'] > 0) {
                $entry = $entries[0];
                $fullname = $entry['cn'][0] ?? $entry['displayname'][0] ?? $username;
                $email = $entry['mail'][0] ?? $username . '@' . $config['domain'];

                $role = Role::where('name', 'Member')->first();

                $user = User::create([
                    'username' => $username,
                    'fullname' => $fullname,
                    'email' => $email,
                    'password' => null, // No password stored
                    'role_id' => $role ? $role->id : 1,
                    'auth_type' => 'ldap',
                    'office_location' => $firstOU,
                ]);
            }
        } elseif (!$isProtectedUser) {
            // refresh the office on every sign-in
            $user->office_location = $firstOU;
            $user->save();
        }

        $branchName = null;
        // sync branches whose names match the user's OUs
        if ($user && !$isProtectedUser) {
            $branches = Branch::whereIn('name', $allOUs)->get();
            $user->branches()->sync($branches->pluck('id')->all());
            $branchName = $branches->first()?->name;
        }

        ldap_close($ldap_conn);

        return $user ? ['user' => $user, 'branch_name' => $branchName] : null;
    }
}
