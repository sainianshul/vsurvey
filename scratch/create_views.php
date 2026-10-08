<?php

$dir = 'c:/Users/anshu/Desktop/code/BKK/vcancares/vcancares/resources/views/admin/nurses/review-steps';
if (!is_dir($dir)) mkdir($dir, 0777, true);

function getHeader($title, $desc) {
    return '
<div class="card-header border-0 pt-8 pb-4">
    <h3 class="card-title align-items-start flex-column">
        <span class="card-label fw-bold fs-2 mb-2 text-gray-900">'.$title.'</span>
        <span class="text-gray-500 fw-semibold fs-7">'.$desc.'</span>
    </h3>
    <div class="card-toolbar">
        @php
            $badgeClass = \'badge-light-warning border-warning text-warning\';
            $badgeText = \'In review\';
            if ($status == 1) {
                $badgeClass = \'badge-light-success border-success text-success\';
                $badgeText = \'Verified\';
            } elseif ($status == 2) {
                $badgeClass = \'badge-light-danger border-danger text-danger\';
                $badgeText = \'Rejected\';
            }
        @endphp
        <span class="badge border fw-bold px-4 py-2 fs-8 {{ $badgeClass }}">{{ $badgeText }}</span>
    </div>
</div>';
}

function getFooter($stepId) {
    return '
<div class="d-flex align-items-center flex-wrap gap-3 mt-8 pt-6 border-top border-gray-200 border-dashed justify-content-end">
    <button type="button" class="btn btn-sm btn-light-danger border border-danger fw-bold px-5 py-2" onclick="processStepReview('.$stepId.', 2)">
        <i class="ki-outline ki-cross fs-5 me-2"></i> Reject
    </button>
    <button type="button" class="btn btn-sm btn-light-success border border-success fw-bold px-5 py-2" onclick="processStepReview('.$stepId.', 1)">
        <i class="ki-outline ki-check fs-5 me-2"></i> Approve this section
    </button>
</div>';
}

// 1.blade.php
$content1 = '<div class="card shadow-none border border-gray-300 bg-white">' .
getHeader('Personal info', 'Review identity and contact details') .
'<div class="card-body pt-0 pb-8">
    <div class="border border-gray-300 border-dashed rounded p-5 mb-4">
        <div class="row g-4">
            <div class="col-md-6">
                <span class="text-gray-500 fs-8 fw-semibold d-block">License Number</span>
                <span class="text-gray-900 fs-6 fw-bold">{{ $sectionData[\'license_number\'] ?? \'N/A\' }}</span>
            </div>
            <div class="col-md-6">
                <span class="text-gray-500 fs-8 fw-semibold d-block">Years of Experience</span>
                <span class="text-gray-900 fs-6 fw-bold">{{ $sectionData[\'years_of_experience\'] ?? \'0\' }} Years</span>
            </div>
            <div class="col-md-12">
                <span class="text-gray-500 fs-8 fw-semibold d-block">Address</span>
                <span class="text-gray-900 fs-6 fw-bold">
                    {{ $sectionData[\'address\'] ?? \'\' }}, {{ $sectionData[\'city\'] ?? \'\' }}, {{ $sectionData[\'state\'] ?? \'\' }}, {{ $sectionData[\'country\'] ?? \'\' }} - {{ $sectionData[\'pincode\'] ?? \'\' }}
                </span>
            </div>
        </div>
    </div>' . getFooter(1) . '
</div></div>';
file_put_contents($dir . '/1.blade.php', $content1);

// 2.blade.php
$content2 = '<div class="card shadow-none border border-gray-300 bg-white">' .
getHeader('Care Types', 'Review selected specializations') .
'<div class="card-body pt-0 pb-8">
    <div class="border border-gray-300 border-dashed rounded p-5 mb-4 bg-light">
        <span class="text-gray-600 fw-semibold fs-7 d-block mb-3">Care Types Selected:</span>
        <div class="d-flex flex-wrap gap-2">
            @foreach($sectionData[\'care_type_ids\'] ?? [] as $id)
                <span class="badge badge-light-primary border border-primary fw-bold px-3 py-2">Care Type #{{ $id }}</span>
            @endforeach
        </div>
    </div>' . getFooter(2) . '
</div></div>';
file_put_contents($dir . '/2.blade.php', $content2);

// 3.blade.php
$content3 = '<div class="card shadow-none border border-gray-300 bg-white">' .
getHeader('Education', 'Review degrees and certifications') .
'<div class="card-body pt-0 pb-8">
    @foreach($sectionData[\'educations\'] ?? [] as $edu)
    <div class="border border-gray-300 rounded p-4 mb-4">
        <div class="d-flex justify-content-between mb-2">
            <span class="text-gray-900 fw-bold fs-6">{{ $edu[\'degree_or_course\'] }}</span>
            <span class="badge badge-light fw-semibold text-gray-700">{{ $edu[\'start_year\'] }} - {{ $edu[\'is_currently_studying\'] ? \'Present\' : $edu[\'end_year\'] }}</span>
        </div>
        <div class="text-gray-700 fw-semibold fs-7">{{ $edu[\'institute_name\'] }}</div>
    </div>
    @endforeach
    ' . getFooter(3) . '
</div></div>';
file_put_contents($dir . '/3.blade.php', $content3);

// 4.blade.php
$content4 = '<div class="card shadow-none border border-gray-300 bg-white">' .
getHeader('Work History', 'Review past employment records') .
'<div class="card-body pt-0 pb-8">
    @foreach($sectionData[\'work_histories\'] ?? [] as $work)
    <div class="border border-gray-300 rounded p-4 mb-4">
        <div class="d-flex justify-content-between mb-2">
            <span class="text-gray-900 fw-bold fs-6">{{ $work[\'role_or_position\'] }}</span>
            <span class="badge badge-light fw-semibold text-gray-700">{{ $work[\'start_date\'] }} - {{ $work[\'is_currently_working\'] ? \'Present\' : $work[\'end_date\'] }}</span>
        </div>
        <div class="text-gray-700 fw-semibold fs-7">{{ $work[\'organization_name\'] }} ({{ $work[\'location\'] }})</div>
    </div>
    @endforeach
    ' . getFooter(4) . '
</div></div>';
file_put_contents($dir . '/4.blade.php', $content4);

// 5.blade.php
$content5 = '<div class="card shadow-none border border-gray-300 bg-white">' .
getHeader('Documents', 'Review uploaded legal documents') .
'<div class="card-body pt-0 pb-8">
    @foreach($sectionData[\'documents\'] ?? [] as $doc)
    <div class="border border-gray-300 rounded p-4 mb-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px bg-light rounded d-flex align-items-center justify-content-center me-4 border border-gray-200">
                <i class="ki-outline ki-document fs-3 text-primary"></i>
            </div>
            <div class="d-flex flex-column">
                <span class="text-gray-900 fw-bold fs-6">{{ $doc[\'document_type_name\'] }}</span>
                <a href="{{ Storage::url($doc[\'file_path\']) }}" target="_blank" class="text-primary fw-semibold fs-8 mt-1 text-hover-primary text-decoration-underline">View Document</a>
            </div>
        </div>
    </div>
    @endforeach
    ' . getFooter(5) . '
</div></div>';
file_put_contents($dir . '/5.blade.php', $content5);

// 6.blade.php
$content6 = '<div class="card shadow-none border border-gray-300 bg-white">' .
getHeader('Availability', 'Review shift preferences and schedules') .
'<div class="card-body pt-0 pb-8">
    <div class="border border-gray-300 border-dashed rounded p-5 mb-4">
        <div class="row g-4">
            <div class="col-md-6">
                <span class="text-gray-500 fs-8 fw-semibold d-block">Available From</span>
                <span class="text-gray-900 fs-6 fw-bold">{{ $sectionData[\'available_from\'] ?? \'N/A\' }}</span>
            </div>
            <div class="col-md-6">
                <span class="text-gray-500 fs-8 fw-semibold d-block">Available To</span>
                <span class="text-gray-900 fs-6 fw-bold">{{ $sectionData[\'available_to\'] ?? \'N/A\' }}</span>
            </div>
        </div>
    </div>
    ' . getFooter(6) . '
</div></div>';
file_put_contents($dir . '/6.blade.php', $content6);

// final.blade.php
$contentFinal = '<div class="card shadow-none border border-gray-300 bg-white">
    <div class="card-header border-0 pt-8 pb-4">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-2 mb-2 text-gray-900">Final Decision</span>
            <span class="text-gray-500 fw-semibold fs-7">Approve or reject the entire application</span>
        </h3>
    </div>
    <div class="card-body pt-0 pb-8">
        <div class="d-flex align-items-center border border-primary border-dashed bg-light-primary rounded p-5 mb-8">
            <i class="ki-outline ki-shield-tick fs-2x text-primary me-4"></i>
            <div class="d-flex flex-column">
                <span class="text-gray-900 fw-bold fs-6 mb-1">Ensure all sections are reviewed</span>
                <span class="text-gray-800 fw-semibold fs-7">Before making a final decision, ensure all 6 sections on the left have been appropriately approved or rejected.</span>
            </div>
        </div>
        <div class="d-flex flex-column gap-3">
            <button type="button" class="btn btn-success fw-bold py-3 fs-6 w-100" onclick="finalizeReview(1)">
                <i class="ki-outline ki-check-circle fs-3 me-2 text-white"></i> Officially Approve Application
            </button>
            <button type="button" class="btn btn-light-danger border border-danger fw-bold py-3 fs-6 w-100" onclick="finalizeReview(2)">
                <i class="ki-outline ki-cross-circle fs-3 me-2"></i> Reject Entire Application
            </button>
        </div>
    </div>
</div>';
file_put_contents($dir . '/final.blade.php', $contentFinal);
