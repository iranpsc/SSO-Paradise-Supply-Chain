@props([
    'type' => 'submit',
    'id' => null,
    'spinnerId' => null,
    'textId' => null,
    'loadingText' => null,
    'variant' => 'primary', // primary, secondary, outline
    'size' => 'default', // default, small, large
    'fullWidth' => false,
    'disabled' => false
])

@php
    $buttonId = $id ?? 'button-' . uniqid();
    
    // Extract custom attributes before merging
    // Try multiple ways to access kebab-case attributes in Blade
    $spinnerIdAttr = $spinnerId ?? $buttonId . '-spinner';
    $textIdAttr = $textId ?? $buttonId . '-text';
    
    // Check attributes bag for spinner-id (kebab-case) or spinnerId (camelCase)
    foreach (['spinner-id', 'spinnerId'] as $key) {
        if ($attributes->has($key)) {
            $spinnerIdAttr = $attributes->get($key);
            break;
        }
    }
    
    // Check attributes bag for text-id (kebab-case) or textId (camelCase)
    foreach (['text-id', 'textId'] as $key) {
        if ($attributes->has($key)) {
            $textIdAttr = $attributes->get($key);
            break;
        }
    }
    
    // Base classes
    $baseClasses = 'rounded-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2';
    
    // Variant classes
    $variantClasses = match($variant) {
        'primary' => 'text-white bg-primery-blue dark:bg-dark-yellow border-primery-blue dark:border-dark-yellow border',
        'secondary' => 'text-primery-blue dark:text-dark-yellow border-primery-blue dark:border-dark-yellow border',
        'outline' => 'text-primery-blue dark:text-dark-yellow border border-primery-blue dark:border-dark-yellow',
        default => 'text-white bg-primery-blue dark:bg-dark-yellow border-primery-blue dark:border-dark-yellow border',
    };
    
    // Size classes
    $sizeClasses = match($size) {
        'small' => 'py-2 px-4 text-sm',
        'large' => 'py-4 px-8 text-lg',
        default => 'py-[14px] px-6 md:px-[40px]',
    };
    
    // Width classes
    $widthClasses = $fullWidth ? 'w-full' : 'md:w-max';
    
    // Spinner color classes based on variant
    $spinnerClasses = match($variant) {
        'primary' => 'text-white dark:text-black',
        'secondary', 'outline' => 'text-primery-blue dark:text-dark-yellow',
        default => 'text-white dark:text-black',
    };
    
    $classes = trim("{$baseClasses} {$variantClasses} {$sizeClasses} {$widthClasses} " . ($attributes->get('class') ?? ''));
@endphp

<button 
    type="{{ $type }}" 
    id="{{ $buttonId }}"
    {{ $attributes->except(['spinner-id', 'text-id', 'spinnerId', 'textId', 'fullWidth', 'loadingText'])->merge(['class' => $classes]) }}
    @if($disabled) disabled @endif
>
    <svg 
        id="{{ $spinnerIdAttr }}" 
        class="hidden animate-spin h-5 w-5 {{ $spinnerClasses }}" 
        xmlns="http://www.w3.org/2000/svg" 
        fill="none" 
        viewBox="0 0 24 24"
    >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span id="{{ $textIdAttr }}">
        {{ $slot }}
    </span>
</button>

