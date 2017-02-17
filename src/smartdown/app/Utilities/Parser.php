<?php namespace Experience\Smartdown\App\Utilities;

use Michelf\MarkdownExtra;
use Michelf\SmartyPants;

class Parser
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \Craft\PluginsService
     */
    protected $pluginsService;

    /**
     * @var MarkdownExtra
     */
    protected $markupParser;

    /**
     * @var SmartyPants
     */
    protected $typographyParser;

    /**
     * Parser constructor.
     *
     * @param MarkdownExtra         $markupParser
     * @param SmartyPants           $typographyParser
     * @param \Craft\PluginsService $pluginsService
     * @param Logger                $logger
     */
    public function __construct(
        MarkdownExtra $markupParser,
        SmartyPants $typographyParser,
        $pluginsService,
        Logger $logger
    ) {
        $this->markupParser = $markupParser;
        $this->typographyParser = $typographyParser;
        $this->pluginsService = $pluginsService;
        $this->logger = $logger;
    }

    /**
     * Runs the given input through all of the available parsers.
     *
     * @param mixed $input
     *
     * @return string
     */
    public function parseAll($input)
    {
        if (!$this->isStringable($input)) {
            $this->logger->logError(__METHOD__ . ' expects a string.');
            return '';
        }

        return $this->parseTypography($this->parseMarkup($input));
    }

    /**
     * Runs the given input through typography parser.
     *
     * @param mixed $input
     *
     * @return string
     */
    public function parseTypography($input)
    {
        if (!$this->isStringable($input)) {
            $this->logger->logError(__METHOD__ . ' expects a string.');
            return '';
        }

        $service = $this->pluginsService;

        // Allow plugins to modify the input.
         $service->call('modifySmartdownTypographyInput', [&$input]);

        $output = $this->typographyParser->transform($input);

        // Allow plugins to modify the output.
        $service->call('modifySmartdownTypographyOutput', [&$output]);

        return $output;
    }

    /**
     * Runs the given input through the markup parser.
     *
     * @param mixed $input
     *
     * @return string
     */
    public function parseMarkup($input)
    {
        if (!$this->isStringable($input)) {
            $this->logger->logError(__METHOD__ . ' expects a string.');
            return '';
        }

        $service = $this->pluginsService;

        // Allow plugins to modify the input.
        $service->call('modifySmartdownMarkupInput', [&$input]);

        $output = $this->markupParser->transform($input);

        // Allow plugins to modify the output.
        $service->call('modifySmartdownMarkupOutput', [&$output]);

        return $output;
    }

    /**
     * Returns a boolean indicating whether the given input can be converted to
     * a string.
     *
     * @param mixed $input
     *
     * @return bool
     */
    protected function isStringable($input)
    {
        return is_string($input) or method_exists($input, '__toString');
    }
}