<?php

namespace App\Filament\Resources\Registrations\Support;

use App\Models\Registration;
use App\Models\RegistrationMember;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationReport
{
    /**
     * @return list<string>
     */
    public static function headings(): array
    {
        return [
            'Reference',
            'Competition',
            'Team name',
            'Participant name',
            'University',
            'Institution',
            'District',
            'Category',
            'Phone',
            'Email',
            'Address',
            'Leader',
            'Members',
            'Tags',
            'Contacted',
            'Paid',
            'Payment transaction',
            'Confirmed',
            'Registered at',
        ];
    }

    /**
     * @param  Builder<Registration>  $query
     */
    public static function download(Builder $query, string $format): StreamedResponse
    {
        return match ($format) {
            'xlsx' => self::xlsx($query),
            'csv' => self::csv($query),
            'pdf' => self::pdf($query),
            default => throw new InvalidArgumentException("Unsupported report format [{$format}]."),
        };
    }

    public static function downloadFromTable(HasTable $table, string $format): StreamedResponse
    {
        $query = $table->getFilteredSortedTableQuery() ?? Registration::query();

        return self::download($query, $format);
    }

    /**
     * @param  Builder<Registration>  $query
     * @return Collection<int, Registration>
     */
    public static function records(Builder $query): Collection
    {
        return (clone $query)
            ->with(['members', 'tags', 'team.leader'])
            ->get();
    }

    /**
     * @return list<string>
     */
    public static function row(Registration $registration): array
    {
        return [
            self::cell($registration->reference_code),
            self::cell($registration->contest_type->value),
            self::cell($registration->team_name),
            self::cell($registration->full_name),
            self::cell($registration->university),
            self::cell($registration->institution_name),
            self::cell($registration->district),
            self::cell($registration->category),
            self::cell($registration->phone),
            self::cell($registration->email),
            self::cell($registration->address),
            self::cell($registration->team?->leader?->full_name),
            self::cell(self::members($registration)),
            self::cell($registration->tags->pluck('name')->implode(', ')),
            $registration->is_contacted ? 'Yes' : 'No',
            $registration->is_paid ? 'Yes' : 'No',
            self::cell($registration->payment_transaction_id),
            $registration->is_confirmed ? 'Yes' : 'No',
            self::cell($registration->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i')),
        ];
    }

    /**
     * @param  Builder<Registration>  $query
     */
    private static function csv(Builder $query): StreamedResponse
    {
        $records = self::records($query);

        return response()->streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, self::headings());

            foreach ($records as $registration) {
                fputcsv($handle, self::row($registration));
            }

            fclose($handle);
        }, self::filename('csv'), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  Builder<Registration>  $query
     */
    private static function xlsx(Builder $query): StreamedResponse
    {
        $records = self::records($query);
        $path = tempnam(sys_get_temp_dir(), 'registrations-report-');

        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(self::headings()));

        foreach ($records as $registration) {
            $writer->addRow(Row::fromValues(self::row($registration)));
        }

        $writer->close();

        return response()->streamDownload(function () use ($path): void {
            try {
                readfile($path);
            } finally {
                @unlink($path);
            }
        }, self::filename('xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  Builder<Registration>  $query
     */
    private static function pdf(Builder $query): StreamedResponse
    {
        $records = self::records($query);

        $pdf = Pdf::loadView('reports.registrations', [
            'headings' => self::headings(),
            'rows' => $records->map(fn (Registration $registration): array => self::row($registration))->all(),
            'generatedAt' => now()->timezone(config('app.timezone')),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, self::filename('pdf'), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private static function members(Registration $registration): string
    {
        return $registration->members
            ->map(function (RegistrationMember $member): string {
                return collect([
                    $member->full_name,
                    $member->phone,
                    filled($member->tshirt_size) ? 'T-shirt '.$member->tshirt_size : null,
                ])->filter()->implode(' · ');
            })
            ->implode('; ');
    }

    private static function cell(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return '';
        }

        $first = $value[0];

        if (in_array($first, ['=', '@'], true)) {
            return "'".$value;
        }

        if (in_array($first, ['+', '-'], true) && ! preg_match('/^[+-]?\d/', $value)) {
            return "'".$value;
        }

        return $value;
    }

    private static function filename(string $extension): string
    {
        return 'registrations-report-'.now()->timezone(config('app.timezone'))->format('Y-m-d-His').'.'.$extension;
    }
}
