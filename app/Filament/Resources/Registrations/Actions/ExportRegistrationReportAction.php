<?php

namespace App\Filament\Resources\Registrations\Actions;

use App\Filament\Resources\Registrations\Support\RegistrationReport;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportRegistrationReportAction
{
    public static function make(): ActionGroup
    {
        return ActionGroup::make([
            self::formatAction('exportExcel', 'Excel', 'xlsx', Heroicon::OutlinedTableCells),
            self::formatAction('exportCsv', 'CSV', 'csv', Heroicon::OutlinedDocumentText),
            self::formatAction('exportPdf', 'PDF', 'pdf', Heroicon::OutlinedDocument),
        ])
            ->label('Export report')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->button()
            ->color('gray')
            ->tooltip('Download the current list, including the active contest tab, search, and filters.');
    }

    private static function formatAction(string $name, string $label, string $format, Heroicon $icon): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->action(fn (HasTable $livewire): StreamedResponse => RegistrationReport::downloadFromTable($livewire, $format));
    }
}
