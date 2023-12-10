<?php

declare(strict_types=1);

namespace Lsp\Mapper;

use Lsp\Contracts\Mapper\Exception\MapperExceptionInterface;
use Lsp\Contracts\Mapper\ExtractorInterface;
use Lsp\Mapper\Exception\ExtractionException;
use Lsp\Mapper\Exception\ExtractorException;
use Lsp\Mapper\Extractor\NormalizerInterface;

final class Extractor implements ExtractorInterface
{
    /**
     * @var list<NormalizerInterface>
     */
    private array $normalizers = [];

    /**
     * @var list<non-empty-string>
     */
    private array $trace = [];

    /**
     * @var list<non-empty-string>
     */
    private array $path = [];

    /**
     * @param iterable<NormalizerInterface> $normalizers
     */
    public function __construct(
        iterable $normalizers = [],
        private readonly bool $skipNulls = false,
        private readonly bool $errorOnRecursion = false,
    ) {
        foreach ($normalizers as $normalizer) {
            try {
                $this->addNormalizer($normalizer);
            } catch (\Throwable $e) {
                throw ExtractorException::fromInternalException($e);
            }
        }
    }

    public function withNormalizer(NormalizerInterface $normalizer): self
    {
        $self = clone $this;
        $self->addNormalizer($normalizer);

        return $self;
    }

    public function addNormalizer(NormalizerInterface $normalizer): void
    {
        $this->normalizers[] = $normalizer;
    }

    public function extract(mixed $data): mixed
    {
        $this->path = $this->trace = [];

        return $this->normalizeAny($data);
    }

    private function normalizeAny(mixed $data): mixed
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->match($data)) {
                try {
                    return $normalizer->normalize($data, $this, $this->path);
                } catch (MapperExceptionInterface $e) {
                    throw $e;
                } catch (\Throwable $e) {
                    throw ExtractionException::fromNormalizerException($e, $this->path);
                }
            }
        }

        return match(true) {
            \is_object($data) => $this->normalizeObject($data),
            \is_array($data) => $this->normalizeArray($data),
            \is_resource($data) => \get_resource_id($data),
            default => $data,
        };
    }

    private function normalizeArray(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if ($value === null && $this->skipNulls) {
                continue;
            }

            $this->path[] = $key;
            $result[$key] = $this->normalizeAny($value);
        }

        return $result;
    }

    private function normalizeObject(object $data): array
    {
        $id = \spl_object_id($data);

        // Avoid recursive normalization
        if (\in_array($id, $this->trace, true)) {
            if ($this->errorOnRecursion) {
                throw ExtractionException::fromObjectRecursion($data, $this->path);
            }

            return [];
        }


        $properties = \get_object_vars($data);

        try {
            $this->trace[] = $id;

            return $this->normalizeArray($properties);
        } finally {
            \array_pop($this->trace);
        }
    }
}
