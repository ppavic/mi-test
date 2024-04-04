<?php

namespace App\Infrastructure;

use App\Domain\Helpers\Seeder;
use App\Domain\Traits\Filter;
use App\Domain\Traits\JSONLFileReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'import',
    description: 'Command imports data to database base on filter criteria or all data, deletes table,
    or imports data with rollback functionality.'    ,
)]
class ImportCommand extends Command
{

   use Filter;

   private Seeder $seeder;

    private array $criteria = [];
    private array $result = [];
    private array $criteriaList = [
        "city",
        'age_min',
        'age_max',
        'children',
        'pets',
    ];

    public function __construct()
    {
        // filter logic
        $this->InitializeLogic();

        // database seeder
        $this->seeder = new Seeder();

        //initialize parent class
        parent::__construct();

    }

    protected function configure()
    {
        $this->addArgument('type', InputArgument::REQUIRED, "Type of import to performe. import-all, import-filterd");
        $this->addOption('children', null, InputOption::VALUE_OPTIONAL, "Number of children. Usage: --children=2:gte");
        $this->addOption('pets', null, InputOption::VALUE_OPTIONAL, "Number of pets. Usage: --pets=1:lte");
        $this->addOption('city', null, InputOption::VALUE_OPTIONAL, "Name of the city. Usage: --city='London':eq");
        $this->addOption('age_min', null, InputOption::VALUE_OPTIONAL, "Minimum age of person:  --age_min=30:gt");
        $this->addOption('age_max', null, InputOption::VALUE_OPTIONAL, "Maximal age of person:  --age_min=30:gt");
        $this->addOption('rollback', null, InputOption::VALUE_NONE, "Rollback transaction.");
        $this->addOption('tableName', null, InputOption::VALUE_OPTIONAL, "Table Name to delete.");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // console output style
        $io = new SymfonyStyle($input, $output);
        $arg = $input->getArgument('type');

        $arg = $input->getArgument('type');

        switch ($arg) {
            case 'import-all':
                $tableNameOption = $input->getOption('tableName');

                if ($tableNameOption == null) {
                    $io->error("Please provide table name to import all data: --tableName='<name>'");
                    return Command::FAILURE;
                }

                //creates table if not exist:
                //attributes named accordin to jsonl data.
                $this->seeder->createTable($tableNameOption, [
                    'Name' => "varchar(255)",
                    'Surname' => "varchar(255)",
                    'DOB' => "date",
                    'Street' => "varchar(255)",
                    'Kids' => 'int',
                    'Pets' => 'int',
                    'City' => 'varchar(255)',
                    'Zip' => 'varchar(255)',
                    'Country' => 'varchar(255)',
                ]);

                // get rollback option
                $rollbackOption = $input->getOption('rollback');

                if ($rollbackOption) {
                    $this->seeder->seed([], $option = 'all', $tableNameOption, true);
                } else {
                    $this->seeder->seed([], $option = 'all', $tableNameOption, false);
                }

                break;

            case 'import-filtered-all':
                $tableNameOption = $input->getOption('tableName');

                if ($tableNameOption == null) {
                    $io->error("Please provide table name for insert: --tableName='<name>'");
                    return Command::FAILURE;
                }

                //creates table if not exist:
                //attributes named accordin to jsonl data.
                $this->seeder->createTable($tableNameOption, [
                    'Name' => "varchar(255)",
                    'Surname' => "varchar(255)",
                    'DOB' => "date",
                    'Street' => "varchar(255)",
                    'Kids' => 'int',
                    'Pets' => 'int',
                    'City' => 'varchar(255)',
                    'Zip' => 'varchar(255)',
                    'Country' => 'varchar(255)',
                ]);

                # get all options and create criteria list
                $this->setCustomOptions($input->getOptions());

                // get rollback option
                $rollbackOption = $input->getOption('rollback');

                if ($rollbackOption) {
                    $this->seeder->seed($this->criteria, $option = 'all', $tableNameOption, true);
                } else {
                    $this->seeder->seed($this->criteria, $option = 'all', $tableNameOption, false);
                }

                break;

            case 'import-filtered-any':
                $tableNameOption = $input->getOption('tableName');

                if ($tableNameOption == null) {
                    $io->error("Please provide table name for insert: --tableName='<name>'");
                    return Command::FAILURE;
                }

                //creates table if not exist:
                //attributes named accordin to jsonl data.
                $this->seeder->createTable($tableNameOption, [
                    'Name' => "varchar(255)",
                    'Surname' => "varchar(255)",
                    'DOB' => "date",
                    'Street' => "varchar(255)",
                    'Kids' => 'int',
                    'Pets' => 'int',
                    'City' => 'varchar(255)',
                    'Zip' => 'varchar(255)',
                    'Country' => 'varchar(255)',
                ]);

                # get all options and create criteria list
                $this->setCustomOptions($input->getOptions());

                // get rollback option
                $rollbackOption = $input->getOption('rollback');

                if ($rollbackOption) {
                    $this->seeder->seed($this->criteria, $option = 'any', $tableNameOption, true);
                } else {
                    $this->seeder->seed($this->criteria, $option = 'any', $tableNameOption, false);
                }

                break;

            case 'delete-table';

                $tableNameOption = $input->getOption('tableName');

                if ($tableNameOption == null) {
                    $io->error("Please provide table name to delete: --tableName='<name>'");
                    Command::FAILURE;
                }

                $this->seeder->dropTable($tableNameOption);

                break;

            default:
                break;
        }
        return Command::SUCCESS;
    }

    private function setCustomOptions(array $options): void
    {

        foreach ($options as $key => $expr) {

            if ($expr != null && in_array($key, $this->criteriaList, true)) {

                $parts = explode(':', $expr);

                // for missing inssert null
                $this->criteria[$key] = ['value' => isset($parts[0]) ? $parts[0] : null, 'logic' => isset($parts[1]) ? $parts[1] : null];
            }
        }
    }

}
