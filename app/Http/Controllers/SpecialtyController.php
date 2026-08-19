<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Specialty;
use App\Support\ClinicSchema;
use Illuminate\Contracts\View\View;

class SpecialtyController extends Controller
{
    /**
     * A clinical area's landing page.
     *
     * This exists for search traffic: someone types their condition, arrives
     * here, and needs three things fast — confirmation the clinic handles it,
     * a sense of what that involves, and the two or three packages that
     * actually suit it. Not the full price list, and not a brochure.
     *
     * Eager-loads services so the packages block costs one extra query rather
     * than one per package.
     */
    public function show(string $slug): View
    {
        $specialty = Specialty::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->with('services')
            ->firstOrFail();

        return view('pages.specialty', [
            // These pages exist for search traffic, so they are the last place
            // that should be missing structured data. Same clinic entity as the
            // homepage — one practice, described identically wherever it is
            // described, which is what @id is for.
            'schema' => ClinicSchema::toJson(),
            'specialty' => $specialty,
            'services' => $specialty->services,
            // The other areas, for the "we also cover" strip. Excluding the
            // current one keeps a visitor from clicking back to where they are.
            'others' => Specialty::active()
                ->where('id', '!=', $specialty->id)
                ->get(),
        ]);
    }
}
