<?php
$steps = [
    1 => ['label' => 'Upload', 'route' => 'kiosk.landing'],
    2 => ['label' => 'Options', 'route' => 'kiosk.options'],
    3 => ['label' => 'Price', 'route' => 'kiosk.price'],
    4 => ['label' => 'Pay', 'route' => 'kiosk.payment.show'],
    5 => ['label' => 'Confirm', 'route' => 'kiosk.confirm.form'],
];
$currentStep = $currentStep ?? 1;
?>
<div class="step-indicator mb-4 position-relative" id="step-indicator">
    <button type="button" class="btn-close position-absolute top-0 end-0" style="font-size: 12px; padding: 4px 8px; opacity: 0.5;" onclick="document.getElementById('step-indicator').style.display='none'" title="Hide steps"></button>
    
    @foreach($steps as $stepNum => $step)
        @php
        $isCompleted = $stepNum < $currentStep;
        $isActive = $stepNum === $currentStep;
        $isDisabled = $stepNum > $currentStep;
        @endphp
        
        @if($isCompleted && isset($printerCode))
            <a href="{{ route($step['route'], ['printerCode' => $printerCode]) }}" class="step completed" title="{{ $step['label'] }}">
                <span class="step-number"><i class="bi bi-check"></i></span>
                <div class="step-label">{{ $step['label'] }}</div>
            </a>
        @elseif($isActive)
            <div class="step active">
                <span class="step-number">{{ $stepNum }}</span>
                <div class="step-label">{{ $step['label'] }}</div>
            </div>
        @else
            <div class="step disabled">
                <span class="step-number">{{ $stepNum }}</span>
                <div class="step-label">{{ $step['label'] }}</div>
            </div>
        @endif
    @endforeach
</div>