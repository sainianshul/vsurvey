<?php
$file = 'c:/Users/anshu/Desktop/code/schotech/vcancares/resources/views/admin/nurses/edit.blade.php';
$content = file_get_contents($file);

// Array of replacements for adding @error
$replacements = [
    'name="name"' => 'name="name" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent ps-10 fs-7 @error(\'name\') is-invalid @enderror"',
    'name="email"' => 'name="email" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent ps-10 fs-7 @error(\'email\') is-invalid @enderror"',
    'name="emergency_contact_phone"' => 'name="emergency_contact_phone" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent ps-10 fs-7 @error(\'emergency_contact_phone\') is-invalid @enderror"',
    'name="bio"' => 'name="bio" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent fs-7 @error(\'bio\') is-invalid @enderror"',
    'name="address"' => 'name="address" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent ps-10 fs-7 @error(\'address\') is-invalid @enderror"',
    'name="city"' => 'name="city" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent fs-7 @error(\'city\') is-invalid @enderror"',
    'name="state"' => 'name="state" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent fs-7 @error(\'state\') is-invalid @enderror"',
    'name="country"' => 'name="country" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent fs-7 @error(\'country\') is-invalid @enderror"',
    'name="pincode"' => 'name="pincode" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent fs-7 @error(\'pincode\') is-invalid @enderror"',
    'name="latitude"' => 'name="latitude" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent fs-7 @error(\'latitude\') is-invalid @enderror"',
    'name="longitude"' => 'name="longitude" class="form-control form-control-sm text-gray-900 border border-gray-300 bg-transparent fs-7 @error(\'longitude\') is-invalid @enderror"',
];

foreach ($replacements as $search => $replace) {
    // We will do a regex to replace the class attribute with the new one
    // Actually, simpler: replace the class="form-control..." with the updated one
}
