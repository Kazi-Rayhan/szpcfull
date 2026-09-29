<?php

namespace Tests\Feature;

use App\Enums\ContestType;
use App\Filament\Resources\Registrations\Pages\ListRegistrations;
use App\Filament\Resources\Registrations\Support\RegistrationReport;
use App\Models\Registration;
use App\Models\Tag;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class RegistrationReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (blank(config('app.key'))) {
            config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        }

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_csv_excel_and_pdf_reports_include_the_registration(): void
    {
        $this->travelTo('2026-09-30 05:07:00');

        $registration = Registration::query()->create([
            'reference_code' => 'SZPC0001',
            'contest_type' => ContestType::Szpc,
            'team_name' => 'Byte Force',
            'full_name' => 'Ada Lovelace',
            'university' => 'University of Global Village',
            'phone' => '+8801711111111',
            'email' => 'ada@example.com',
            'is_contacted' => true,
            'is_paid' => true,
            'payment_transaction_id' => 'TRX123',
            'is_confirmed' => false,
        ]);

        $registration->members()->create([
            'sort_order' => 1,
            'full_name' => 'Ada Lovelace',
            'phone' => '+8801711111111',
            'tshirt_size' => 'M',
        ]);

        $registration->tags()->attach(Tag::query()->create([
            'name' => 'Finalist',
            'color' => 'amber',
        ]));

        $csv = $this->capture(RegistrationReport::download(Registration::query(), 'csv'));

        $this->assertStringContainsString('SZPC0001', $csv);
        $this->assertStringContainsString('Byte Force', $csv);
        $this->assertStringContainsString('Finalist', $csv);
        $this->assertStringContainsString('Ada Lovelace · +8801711111111 · T-shirt M', $csv);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);

        $xlsx = $this->capture(RegistrationReport::download(Registration::query(), 'xlsx'));
        $rows = $this->readXlsx($xlsx);

        $this->assertSame('Reference', $rows[0][0]);
        $this->assertContains('SZPC0001', $rows[1]);
        $this->assertContains('TRX123', $rows[1]);

        $pdf = $this->capture(RegistrationReport::download(Registration::query(), 'pdf'));

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_spreadsheet_cells_escape_formula_text(): void
    {
        $registration = Registration::query()->create([
            'reference_code' => 'SZPC0002',
            'contest_type' => ContestType::Jpc,
            'team_name' => '=CMD()',
            'phone' => '+8801711111112',
        ]);

        $row = RegistrationReport::row($registration->load('members', 'tags', 'team.leader'));

        $this->assertSame("'=CMD()", $row[2]);
        $this->assertSame('+8801711111112', $row[8]);
    }

    public function test_list_page_downloads_the_filtered_report(): void
    {
        $this->travelTo('2026-09-30 05:07:00');
        $this->actingAs(User::factory()->create());

        Registration::query()->create([
            'reference_code' => 'SZPC0001',
            'contest_type' => ContestType::Szpc,
            'team_name' => 'Byte Force',
            'phone' => '01700000001',
        ]);

        Registration::query()->create([
            'reference_code' => 'JPC0001',
            'contest_type' => ContestType::Jpc,
            'team_name' => 'Junior Squad',
            'phone' => '01700000002',
        ]);

        $component = Livewire::test(ListRegistrations::class)
            ->assertSee('Export report')
            ->assertSee('Excel')
            ->assertSee('CSV')
            ->assertSee('PDF')
            ->set('activeTab', ContestType::Szpc->value)
            ->callAction(TestAction::make('exportCsv')->table());

        $component->assertFileDownloaded('registrations-report-2026-09-30-050700.csv');

        $csv = base64_decode((string) data_get($component->effects, 'download.content'));

        $this->assertStringContainsString('SZPC0001', $csv);
        $this->assertStringNotContainsString('JPC0001', $csv);
    }

    private function capture(StreamedResponse $response): string
    {
        ob_start();
        $response->sendContent();

        return (string) ob_get_clean();
    }

    /**
     * @return list<list<string>>
     */
    private function readXlsx(string $contents): array
    {
        $path = tempnam(sys_get_temp_dir(), 'registrations-report-test-');
        file_put_contents($path, $contents);

        $reader = new Reader;
        $reader->open($path);

        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = array_map(strval(...), $row->toArray());
            }

            break;
        }

        $reader->close();
        @unlink($path);

        return $rows;
    }
}
