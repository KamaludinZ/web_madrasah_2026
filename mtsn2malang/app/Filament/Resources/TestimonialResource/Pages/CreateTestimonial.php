<?php

namespace App\Filament\Resources\TestimonialResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\TestimonialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTestimonial extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = TestimonialResource::class;
}
