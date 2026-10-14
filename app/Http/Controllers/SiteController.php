<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use SplFileInfo;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('coming-soon', [
            'seoPage' => 'home',
            'seoTitle' => '4th SZPC — Coming soon',
            'images' => $this->galleryImages(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function galleryImages(): array
    {
        $order = ['one' => 1, 'two' => 2, 'three' => 3];

        return collect(File::files(public_path('gallery')))
            ->filter(fn (SplFileInfo $file): bool => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
            ->sortBy(fn (SplFileInfo $file): int => $order[strtolower($file->getBasename('.'.$file->getExtension()))] ?? 100)
            ->map(fn (SplFileInfo $file): string => asset('gallery/'.$file->getFilename()))
            ->values()
            ->all();
    }

    public function segment(): View
    {
        return view('segment', [
            'seoPage' => 'home',
            'seoTitle' => request()->query('s') === 'jpc'
                ? "JPC-2026 — Junior Programming Contest | SZPC '26"
                : (request()->query('s') === 'ithq'
                    ? "ITHQ-2026 — ICT Talent Hunt | SZPC '26"
                    : "SZPC-2026 — University Programming Contest | SZPC '26"),
        ]);
    }
}
