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

use Laminas\ServiceManager\Factory\FactoryInterface;
use Mezzio\Helper\ServerUrlHelper;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;
use function is_string;

/**
 * Generates the BootstrapFlashMessenger view helper object
 */
final class ViteUrlFactory implements FactoryInterface
{
    /**
     * Create Service Factory
     *
     * @param array<mixed>|null $options
     * @phpstan-param array<mixed>|null $options
     *
     * @throws ContainerExceptionInterface
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array | null $options = null,
    ): ViteUrl {
        $config = $container->get('config');
        assert(is_array($config));

        $config = $config['vite-url'] ?? [];
        assert(is_array($config));

        $publicDir = $config['public-dir'] ?? null;

        if (!is_string($publicDir)) {
            $publicDir = null;
        }

        $buildDir = $config['build-dir'] ?? null;

        if (!is_string($buildDir)) {
            $buildDir = null;
        }

        $viteHost = $config['vite-host'] ?? null;

        if (!is_string($viteHost)) {
            $viteHost = null;
        }

        $serverUrl = $container->get(ServerUrlHelper::class);
        assert($serverUrl instanceof ServerUrlHelper);

        return new ViteUrl($serverUrl, $publicDir, $buildDir, $viteHost);
    }
}
