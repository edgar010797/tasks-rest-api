<?php

return [
    'forbidden' => 'You do not have access to this page.',
    'unauthenticated' => 'Login required',

    'jwt' => [
        'token_created' => 'Token successfully created',
        'token_invalid' => 'Token is invalid',
        'token_expired' => 'Token has expired',
        'token_not_found' => 'Token not found',
        'token_blacklisted' => 'Token is blacklisted',
        'token_cannot_create' => 'Unable to create token',
        'token_cannot_invalidate' => 'Unable to invalidate token',
        'token_general' => 'Token error. Please log in again.',
    ],

    'register' => [
        'success' => 'User registered successfully',
        'error' => 'Unable to register user',
    ],

    'login' => [
        'success' => 'Login successful',
        'invalid_credentials' => 'Invalid email or password',
        'error' => 'Unable to log in',
        'failed' => 'Invalid credentials.',
        'password' => 'The provided password is incorrect.',
    ],

    'logout' => [
        'success' => 'Logout successful',
        'error' => 'Unable to log out',
    ],

    'user' => [
        'not_found' => 'User not found',
        'profile_error' => 'Unable to retrieve user profile',
        'update_success' => 'User data updated',
        'update_error' => 'Unable to update user data',
    ],

    'validation' => [
        'required' => 'The :attribute field is required.',
        'email' => 'The :attribute must be a valid email address.',
        'unique' => 'The :attribute has already been taken.',
        'min' => 'The :attribute must be at least :min characters.',
        'confirmed' => 'The :attribute confirmation does not match.',
    ],
];
