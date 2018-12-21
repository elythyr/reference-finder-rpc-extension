<?php

namespace Phpactor\Extension\ReferenceFinderRpc\Handler;

use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\WorseReflectionExtra\GotoDefinition\GotoDefinition;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;

class GotoDefinitionHandler implements Handler
{
    const NAME = 'goto_definition';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';
    const PARAM_LANGUAGE = 'language';

    /**
     * @var DefinitionLocator
     */
    private $locator;

    public function __construct(
        DefinitionLocator $locator
    ) {
        $this->locator = $locator;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver)
    {
        $resolver->setDefaults([
            self::PARAM_LANGUAGE => 'php',
        ]);
        $resolver->setRequired([
            self::PARAM_OFFSET,
            self::PARAM_SOURCE,
            self::PARAM_PATH,
        ]);
    }

    public function handle(array $arguments)
    {
        $document = TextDocumentBuilder::create($arguments[self::PARAM_SOURCE])
            ->uri($arguments[self::PARAM_PATH])
            ->language(self::PARAM_LANGUAGE)->build();

        $offset = ByteOffset::fromInt($arguments[self::PARAM_OFFSET]);
        $location = $this->locator->locateDefinition($document, $offset);

        return OpenFileResponse::fromPathAndOffset(
            $location->uri()->__toString(),
            $location->offset()->toInt()
        );
    }
}
