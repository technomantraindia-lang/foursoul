<?php

// Phase 12 — Part 12: Feature flag defaults.
// Runtime overrides live in cache via FeatureToggleService; kill switches
// are env-driven as FEATURE_{KEY}_DISABLED=true.

return [

    'flags' => [
        // 'example-feature' => ['enabled' => false, 'type' => 'standard'],
        // Types: standard | development | beta | hidden
    ],

];