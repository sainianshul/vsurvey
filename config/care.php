<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Nurse Matching Configuration
    |--------------------------------------------------------------------------
    */

    // Maximum distance in kilometers to search for available nurses
    'matching_radius_km' => 5,

    // Time flexibility in hours
    'time_flexibility_hours' => 1,

    // Maximum distance in meters a nurse can be from the patient to force end a session
    'maximum_diameter_for_force_end' => 100,

    // Minimum hours before a care request can start
    'min_booking_notice_hours' => 6,

    // Maximum days in advance a user can schedule
    'max_booking_advance_days' => 4,

    // Matching attempts — order of execution
    'matching_attempts' => [
        ['radius_km' => 5,  'time_flex_hours' => 0],
        ['radius_km' => 5,  'time_flex_hours' => 1],
        ['radius_km' => 7,  'time_flex_hours' => 0],
        ['radius_km' => 7,  'time_flex_hours' => 1],
        ['radius_km' => 10, 'time_flex_hours' => 0],
        ['radius_km' => 10, 'time_flex_hours' => 1],
    ],

    /*
    |--------------------------------------------------------------------------
    | Commission Configuration
    |--------------------------------------------------------------------------
    */
    'commission' => [
        'type' => 1,   // 1=percentage, 2=flat
        'value' => 15,  // 15%
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    */

    // Active payment gateway name (for logging)
    'payment_gateway' => env('PAYMENT_GATEWAY', 'razorpay'),

    // Currency (ISO 4217)
    'currency' => 'INR',

    /*
    |--------------------------------------------------------------------------
    | Cancellation Policy
    |--------------------------------------------------------------------------
    |
    | Slab-based refund percentages based on hours before next session.
    | Ordered from highest to lowest. First match wins.
    |
    */
    'cancellation_slabs' => [
        ['hours_before' => 24, 'refund_percent' => 100],
        ['hours_before' => 12, 'refund_percent' => 85],
        ['hours_before' => 2,  'refund_percent' => 50],
        ['hours_before' => 0,  'refund_percent' => 0],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nurse Discipline
    |--------------------------------------------------------------------------
    */

    // Auto-suspend nurse after this many booking cancellations
    'nurse_cancel_strike_limit' => 3,

    /*
    |--------------------------------------------------------------------------
    | Withdrawal Configuration
    |--------------------------------------------------------------------------
    */

    // Minimum withdrawal amount (in rupees)
    'min_withdrawal_amount' => 100,

    // Extension priority window (minutes)
    'extension_priority_window_minutes' => 30,

];
