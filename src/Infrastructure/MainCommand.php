<?php

namespace App\Infrastructure;

use App\Domain\Helpers\JSONLFileReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'filter',
    description: 'Command that loads data from var/input.jsonl and filters data under given criteria.
            Criteria is set by using the option. <type> of filters are all or any.
            <type> "all" will return results that satisfy each criteria.
            <type> "any" will return result while satissfying at least one of criteria.
            Running command without any options, returns all results regardless of <type>.
            Example: php bin/app filter all --pets=3:eq.
            Filtering logic: gt -> greather than, lt->les than, eq -> equal....'
    ,
)]
class MainCommand extends Command
{

    use JSONLFileReader;

    private const FILE_NAME = 'var/input.jsonl';

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
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('type', InputArgument::REQUIRED, "Type of data query. 'all' or 'any'.");
        $this->addOption('children', null, InputOption::VALUE_OPTIONAL, "Number of children. Usage: --children=2:gte");
        $this->addOption('pets', null, InputOption::VALUE_OPTIONAL, "Number of pets. Usage: --pets=1:lte");
        $this->addOption('city', null, InputOption::VALUE_OPTIONAL, "Name of the city. Usage: --city='London':eq");
        $this->addOption('age_min', null, InputOption::VALUE_OPTIONAL, "Minimum age of person:  --age_min=30:gt");
        $this->addOption('age_max', null, InputOption::VALUE_OPTIONAL, "Maximal age of person:  --age_min=30:gt");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // console output style
        $io = new SymfonyStyle($input, $output);
        $arg = $input->getArgument('type');

        //check if file exists
        if (!file_exists($this::FILE_NAME)) {
            throw new \ErrorException("File does not exist! File: " . $this::FILE_NAME);
            return Command::FAILURE;
        }

        $arg = $input->getArgument('type');

        switch ($arg) {
            case 'all':
                $io->info('Filter type: all. All conditions are met.');

                # get all options and create criteria list
                $this->setCustomOptions($input->getOptions());

                // Read JSONL
                $data = $this->readJSONLFile($this::FILE_NAME);

                break;

            case 'any':
                $io->info('Filter type: any. Any of conditions are met.');

                # get all options and create criteria list
                $this->setCustomOptions($input->getOptions());

                // Read JSONL
                $data = $this->readJSONLFile($this::FILE_NAME);

                break;

            default:
                $io->info('Filter type: all (default). All conditions are met.');

                # get all options and create criteria list
                $this->setCustomOptions($input->getOptions());

                // Read JSONL
                $data = $this->readJSONLFile($this::FILE_NAME);

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
