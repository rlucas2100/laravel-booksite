<?php

/**
 * checks if the logged-in user as an admin or super-admin
 */
if (! function_exists('isAdmin')) {
    function isAdmin() {
        return auth()->check() && auth()->user()->hasRole(['Super-Admin', 'Admin']);
    }
}

