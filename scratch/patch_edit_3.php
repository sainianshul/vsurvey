<?php
$file = 'c:/Users/anshu/Desktop/code/schotech/vcancares/resources/views/admin/nurses/edit.blade.php';
$content = file_get_contents($file);

$fields = [
    'name', 'email', 'phone', 'emergency_contact_phone', 'bio', 
    'address', 'city', 'state', 'country', 'pincode', 'latitude', 'longitude',
    'available_from', 'available_to'
];

foreach ($fields as $field) {
    // Add is-invalid class if error exists
    $content = preg_replace(
        '/(name="'.$field.'".*?class=")([^"]*)(")/i',
        '$1$2 @error(\''.$field.'\') is-invalid @enderror$3',
        $content
    );
    
    // Add error message div after the input tag (which ends with /> or >)
    // To safely match the whole input tag, we can match <input ... name="field" ... />
    // We will just match until the next " />" or ">" that isn't part of ->
    // Alternatively, we can use preg_replace_callback
    $content = preg_replace_callback(
        '/<input\s+[^>]*name="'.$field.'"[^>]*>/i',
        function ($matches) use ($field) {
            $tag = $matches[0];
            // If the tag contains "->", our simple regex might not have captured the whole tag.
            // But HTML regex is hard. Let's just find the end of the input tag in the original content!
            return $tag; 
        },
        $content
    );
}

// Actually, let's just do str_replace for each input!
file_put_contents($file, $content);
echo "Done\n";
