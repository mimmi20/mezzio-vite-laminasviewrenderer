<?php

/**
 * This file is part of the mimmi20/mezzio-vite-laminasviewrenderer package.
 *
 * Copyright (c) 2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\LaminasView\ViteUrl\View\Helper;

use JsonException;
use Laminas\View\Exception\RuntimeException;
use Mezzio\Helper\ServerUrlHelper;

use function file_get_contents;
use function is_file;
use function json_decode;
use function mb_ltrim;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final readonly class ViteUrl
{
    /** @throws void */
    public function __construct(
        private ServerUrlHelper $serverUrl,
        private string | null $publicDir,
        private string | null $buildDir,
        private string | null $viteHost = null,
    ) {
        // nothing to do
    }

    /** @throws void */
    public function __invoke(): self
    {
        return $this;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getPublicDir(): string | null
    {
        return $this->publicDir;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getBuildDir(): string | null
    {
        return $this->buildDir;
    }

    /**
     * @throws RuntimeException
     *
     * @api
     */
    public function file(string $name): string
    {
        if ($this->publicDir === null) {
            throw new RuntimeException('A Public Dir is required');
        }

        if ($this->viteHost) {
            return $this->viteHost . '/' . mb_ltrim($name, '/');
        }

        $manifest = $this->manifestContents();

        if (!isset($manifest[$name]['file'])) {
            throw new RuntimeException('Unknown Vite entrypoint ' . $name);
        }

        return $this->serverUrl->generate('/' . $this->buildDir . '/' . $manifest[$name]['file']);
    }

    /**
     * @throws void
     *
     * @api
     */
    public function isDev(): bool
    {
        return !empty($this->viteHost);
    }

    /**
     * Retrieve our manifest file contents.
     *
     * @return array<string, array{file: string, imports: array<string, mixed>, css: array<string, mixed>}>
     *
     * @throws RuntimeException
     */
    private function manifestContents(): array
    {
        if ($this->buildDir === null) {
            throw new RuntimeException('A Build Dir is required');
        }

        $manifestPath = $this->publicDir . '/' . $this->buildDir . '/.vite/manifest.json';

        if (!is_file($manifestPath)) {
            throw new RuntimeException(
                sprintf('Vite manifest not found at %s', $manifestPath),
            );
        }

        $content = file_get_contents($manifestPath);

        if (!$content) {
            throw new RuntimeException(
                sprintf('Could not read Vite manifest at: %s', $manifestPath),
            );
        }

        try {
            return json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException(
                sprintf('Could not decode Vite manifest at: %s', $manifestPath),
                0,
                $e,
            );
        }
    }
}
