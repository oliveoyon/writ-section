<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StaffUserSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'password';

    /**
     * Paste staff/admin rows here.
     *
     * Required: name, employee_id, department
     * Optional: card_id, user_type
     */
    private array $rows = [
        ['name' => 'Nasima Khatun', 'employee_id' => '7019', 'department' => 'Superintendent', 'card_id' => '0012837793'],
        ['name' => 'Pradip Kumer Chakravorty', 'employee_id' => '8489', 'department' => 'Superintendent', 'card_id' => '0012855875'],
        ['name' => 'Md. Shafique Ahmed', 'employee_id' => '7035', 'department' => 'Superintendent', 'card_id' => '0012889224'],
        ['name' => 'Safiqul Islam', 'employee_id' => '7164', 'department' => 'Ready Table', 'card_id' => '0012823777'],
        ['name' => 'Md. Mojibur Rahman', 'employee_id' => '8490', 'department' => 'Ready Table', 'card_id' => '0012855867'],
        ['name' => 'Md. Altaf Hossain', 'employee_id' => '8844', 'department' => 'Ready Table', 'card_id' => '0012889222'],
        ['name' => 'Md. Arshadur Rahman', 'employee_id' => '7176', 'department' => 'Ready Table', 'card_id' => '0003969670'],
        ['name' => 'Md. Kazi Iqbal Kabir', 'employee_id' => '7215', 'department' => 'Put-Up Section', 'card_id' => '0003967436'],
        ['name' => 'Md. Mahatab Hossain', 'employee_id' => '7095', 'department' => 'Put-Up Section', 'card_id' => '0003969680'],
        ['name' => 'Md. Mahbub Alam', 'employee_id' => '8597', 'department' => 'Put-Up Section', 'card_id' => '0003943360'],
        ['name' => 'Md. Kawsar Ali', 'employee_id' => '8638', 'department' => 'Put-Up Section', 'card_id' => '0003967431'],
        ['name' => 'Mohammad Helal Uddin', 'employee_id' => '8649', 'department' => 'Put-Up Section', 'card_id' => '0003970579'],
        ['name' => 'Md. Foridul Islam', 'employee_id' => '7189', 'department' => 'Put-Up Section', 'card_id' => '0012802233'],
        ['name' => 'Nur Mohammad', 'employee_id' => '8926', 'department' => 'Put-Up Section', 'card_id' => '0003969675'],
        ['name' => 'Tuhinul Haque', 'employee_id' => '7172', 'department' => 'Put-Up Section', 'card_id' => '0012850667'],
        ['name' => 'Kaiyum Ali', 'employee_id' => '7300', 'department' => 'Dealing Assistant', 'card_id' => '0003943356'],
        ['name' => 'Md. Nazmul Hoque', 'employee_id' => '5053', 'department' => 'Dealing Assistant', 'card_id' => '0003967432'],
        ['name' => 'Saiful Islam', 'employee_id' => '9293', 'department' => 'Dealing Assistant', 'card_id' => '0003969674'],
        ['name' => 'Md. Riyad Ahmed', 'employee_id' => '7288', 'department' => 'Dealing Assistant', 'card_id' => '0003969677'],
        ['name' => 'Md. Khalil Ullah Joni', 'employee_id' => '5139', 'department' => 'Dealing Assistant', 'card_id' => '0003943357'],
        ['name' => 'Md. Shakilur Rahman', 'employee_id' => '8881', 'department' => 'Dealing Assistant', 'card_id' => '0003969671'],
        ['name' => 'Md. Akayed Akandha', 'employee_id' => '5083', 'department' => 'Dealing Assistant', 'card_id' => '0003967435'],
        ['name' => 'Md. Masud Rana', 'employee_id' => '8882', 'department' => 'Dealing Assistant', 'card_id' => '0003969679'],
        ['name' => 'Md. Kamal Akon', 'employee_id' => '5101', 'department' => 'Compare Section', 'card_id' => '0003943359'],
        ['name' => 'Gopal Saha', 'employee_id' => '8774', 'department' => 'Compare Section', 'card_id' => '0003969672'],
        ['name' => 'Md. Soabur Rahman', 'employee_id' => '7098', 'department' => 'Compare Section', 'card_id' => '0003967434'],
        ['name' => 'Md. Omar Ali', 'employee_id' => '7030', 'department' => 'Compare Section', 'card_id' => '0003943358'],
        ['name' => 'Md. Mizanur Rahaman', 'employee_id' => '5061', 'department' => 'Scanning', 'card_id' => '0003969678'],
        ['name' => 'Md. Shah Alam Howlader', 'employee_id' => '8830', 'department' => 'Requisite Section', 'card_id' => '0003967433'],
        ['name' => 'Md. Ekram', 'employee_id' => '8288', 'department' => 'Filing Section', 'card_id' => '0003969673'],
        ['name' => 'Shadekur Rahoman Sadek', 'employee_id' => '9940', 'department' => 'Affidavit Section', 'card_id' => '0012850663'],
        ['name' => 'Md. Asaduzzaman', 'employee_id' => '8619', 'department' => 'Record Room', 'card_id' => '0012850655'],
        ['name' => 'Md. Ashikur Rahman', 'employee_id' => '9202', 'department' => 'Record Room', 'card_id' => '0012855937'],
        ['name' => 'Md. Abdul Momen', 'employee_id' => '9048', 'department' => 'Record Room', 'card_id' => '0012855929'],
        ['name' => 'Md. Yousuf Hossain', 'employee_id' => '9162', 'department' => 'Record Room', 'card_id' => '0012850654'],
        ['name' => 'Md. Shafiul Alam', 'employee_id' => '9338', 'department' => 'Record Room', 'card_id' => '0012850662'],
        ['name' => 'Md. Nur A Alam', 'employee_id' => '9231', 'department' => 'Record Room', 'card_id' => '0012855936'],
        ['name' => 'Md. Jaber Hossain', 'employee_id' => '8839', 'department' => 'Record Room', 'card_id' => '0012855928'],
        ['name' => 'Md. Abul Khair', 'employee_id' => '9059', 'department' => 'Case Docket', 'card_id' => '0012850661'],
        ['name' => 'Jinnah Patwary', 'employee_id' => '9266', 'department' => 'Affidavit Section', 'card_id' => '0012850653'],
        ['name' => 'Md. Miraz', 'employee_id' => '9141', 'department' => 'Process Service', 'card_id' => '0012850659'],
        ['name' => 'Nurul Amin', 'employee_id' => '9345', 'department' => 'Process Service', 'card_id' => '0012855874'],
        ['name' => 'Md. Nazmul', 'employee_id' => '9077', 'department' => 'Process Service', 'card_id' => '0012889236'],
        ['name' => 'Md. Shahin Sheikh', 'employee_id' => '9019', 'department' => 'Process Service', 'card_id' => '0012850669'],
        ['name' => 'Md. Rayhan', 'employee_id' => '9133', 'department' => 'Others', 'card_id' => '0003969676'],
        ['name' => 'Md. Jahangir Hossain', 'employee_id' => '9140', 'department' => 'Others', 'card_id' => '0012855934'],
        ['name' => 'Jhontu Chandra Howlader', 'employee_id' => '9405', 'department' => 'Others', 'card_id' => '0012850668'],
        ['name' => 'Shamima Khanom', 'employee_id' => '9640', 'department' => 'Others', 'card_id' => '0012850660'],
        ['name' => 'Lipika Chowdhury Sajib', 'employee_id' => '9886', 'department' => 'Typing Section', 'card_id' => null],
        ['name' => 'Md. Abdul Halim', 'employee_id' => '8781', 'department' => 'Typing Section', 'card_id' => null],
        ['name' => 'Md. Sharif Mia', 'employee_id' => '9934', 'department' => 'Typing Section', 'card_id' => null],
        ['name' => 'Md. Abdul Wahab', 'employee_id' => '8789', 'department' => 'Typing Section', 'card_id' => null],
        ['name' => 'Mayhedi Hasan Monir', 'employee_id' => '8842', 'department' => 'Typing Section', 'card_id' => null],
        ['name' => 'Md. Hanif Mia', 'employee_id' => '9938', 'department' => 'Typing Section', 'card_id' => null],
        ['name' => 'Osman Gani', 'employee_id' => '5071', 'department' => 'Others', 'card_id' => null],
        ['name' => 'Md. Ashaduzzaman', 'employee_id' => '9432', 'department' => 'Others', 'card_id' => null],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            foreach (Department::CANONICAL_NAMES as $name) {
                Department::firstOrCreate(
                    ['name' => $name],
                    ['display_name' => $name]
                );
            }

            $this->validateRows();

            $roles = collect(['Admin', 'Staff'])
                ->mapWithKeys(fn (string $name) => [
                    $name => Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
                ]);

            foreach ($this->rows as $row) {
                $employeeId = trim((string) ($row['employee_id'] ?? ''));
                $departmentName = trim((string) ($row['department'] ?? ''));

                if ($employeeId === '' || $departmentName === '') {
                    continue;
                }

                $department = Department::where('name', $departmentName)
                    ->orWhere('display_name', $departmentName)
                    ->first();

                if (!$department) {
                    throw new \RuntimeException("Department not found: {$departmentName}");
                }

                $requestedType = strtolower(trim((string) ($row['user_type'] ?? 'staff')));
                $userType = $this->resolveUserTypeForDepartment($requestedType, $department);

                $user = User::firstOrNew(['employee_id' => $employeeId]);
                $user->forceFill([
                    'name' => trim((string) ($row['name'] ?? $employeeId)),
                    'email' => strtolower($employeeId) . '@email.com',
                    'login_id' => trim((string) ($row['card_id'] ?? '')) ?: null,
                    'department' => (string) $department->id,
                    'password' => $user->exists ? $user->password : Hash::make(self::DEFAULT_PASSWORD),
                    'user_type' => $userType,
                    'is_active' => true,
                ])->save();

                $user->syncRoles([$roles->get('Staff')]);
            }
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function resolveUserTypeForDepartment(string $requestedType, Department $department): string
    {
        return 'staff';
    }

    private function validateRows(): void
    {
        $employeeIds = [];
        $cardIds = [];

        foreach ($this->rows as $row) {
            $employeeId = trim((string) ($row['employee_id'] ?? ''));
            $cardId = trim((string) ($row['card_id'] ?? ''));

            if ($employeeId !== '') {
                $employeeIds[$employeeId] = ($employeeIds[$employeeId] ?? 0) + 1;
            }

            if ($cardId !== '') {
                $cardIds[$cardId] = ($cardIds[$cardId] ?? 0) + 1;
            }
        }

        $duplicateEmployeeIds = array_keys(array_filter($employeeIds, fn (int $count) => $count > 1));
        $duplicateCardIds = array_keys(array_filter($cardIds, fn (int $count) => $count > 1));

        if ($duplicateEmployeeIds || $duplicateCardIds) {
            throw new \RuntimeException(sprintf(
                'Duplicate import data. Employee IDs: %s. Card IDs: %s.',
                implode(', ', $duplicateEmployeeIds) ?: 'none',
                implode(', ', $duplicateCardIds) ?: 'none'
            ));
        }

        foreach ($cardIds as $cardId => $count) {
            $existing = User::where('login_id', $cardId)
                ->whereNotIn('employee_id', array_keys($employeeIds))
                ->first(['name', 'employee_id']);

            if ($existing) {
                throw new \RuntimeException("Card ID {$cardId} is already assigned to {$existing->name}.");
            }
        }
    }
}
