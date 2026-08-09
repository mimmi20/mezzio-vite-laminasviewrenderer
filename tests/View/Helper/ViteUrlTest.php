<?php

/**
 * This file is part of the mimmi20/mezzio-vite-laminasviewrenderer package.
 *
 * Copyright (c) 2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\LaminasView\ViteUrl\View\Helper;

use Laminas\View\Exception\RuntimeException;
use Mezzio\Helper\ServerUrlHelper;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use function json_encode;
use function sprintf;

final class ViteUrlTest extends TestCase
{
    /** @throws ExpectationFailedException */
    public function testInvoke(): void
    {
        $publicDir = 'test-public-dir';
        $buildDir  = 'test-build-dir';

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $publicDir, $buildDir);

        self::assertSame($object, $object());
    }

    /** @throws RuntimeException */
    public function testFileWithoutPublicDir(): void
    {
        $publicDir = null;
        $buildDir  = null;

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $publicDir, $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A Public Dir is required');

        $object->file('');
    }

    /**
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testFileWithHotRelaoding(): void
    {
        $root   = vfsStream::setup('root');
        $hotUrl = 'https://test.hot.dir';
        $name   = 'test.js';

        $buildDir = 'test-build-dir';
        $file     = 'test-xyz.js';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([$name => ['file' => $file]]));

        $dir = vfsStream::newDirectory($buildDir);
        $dir->addChild($file1);

        $root->addChild($dir);

        $buildDir = 'test-build-dir';

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $root->url(), $buildDir, $hotUrl);

        self::assertTrue($object->isDev());
        self::assertSame($hotUrl . '/' . $name, $object->file($name));
    }

    /** @throws RuntimeException */
    public function testFileWithHotRelaoding3(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('hot', 0777);
        $file1->setContent('');

        $root->addChild($file1);

        $manifestPathV5 = $root->url() . '/' . $buildDir . '/.vite/manifest.json';

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $root->url(), $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Vite manifest not found at %s', $manifestPathV5),
        );

        $object->file($name);
    }

    /** @throws RuntimeException */
    public function testFileWithHotRelaoding4(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('hot', 0333);
        $file1->setContent('');

        $root->addChild($file1);

        $manifestPathV5 = $root->url() . '/' . $buildDir . '/.vite/manifest.json';

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $root->url(), $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Vite manifest not found at %s', $manifestPathV5),
        );

        $object->file($name);
    }

    /**
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testFileWithHotRelaoding5(): void
    {
        $root   = vfsStream::setup('root');
        $hotUrl = 'https://test.hot.dir';
        $name   = 'test.js';
        $name2  = '@vite/client';

        $buildDir = 'test-build-dir';
        $file     = 'test-xyz.js';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([$name => ['file' => $file]]));

        $dir2 = vfsStream::newDirectory('.vite');
        $dir2->addChild($file1);

        $dir1 = vfsStream::newDirectory($buildDir);
        $dir1->addChild($dir2);

        $root->addChild($dir1);

        $buildDir = 'test-build-dir';

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $root->url(), $buildDir, $hotUrl);

        self::assertTrue($object->isDev());
        self::assertSame($hotUrl . '/' . $name2, $object->file($name2));
    }

    /** @throws RuntimeException */
    public function testFileWithoutManifest(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $dir2 = vfsStream::newDirectory('.vite');

        $dir1 = vfsStream::newDirectory($buildDir);
        $dir1->addChild($dir2);

        $root->addChild($dir1);

        $publicDir      = 'test-public-dir';
        $manifestPathV5 = $publicDir . '/' . $buildDir . '/.vite/manifest.json';

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $publicDir, $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Vite manifest not found at %s', $manifestPathV5),
        );

        $object->file($name);
    }

    /** @throws RuntimeException */
    public function testFileWithManifest(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([]));

        $dir2 = vfsStream::newDirectory('.vite');
        $dir2->addChild($file1);

        $dir1 = vfsStream::newDirectory($buildDir);
        $dir1->addChild($dir2);

        $root->addChild($dir1);

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $root->url(), $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Unknown Vite entrypoint %s', $name));

        $object->file($name);
    }

    /** @throws RuntimeException */
    public function testFileWithManifest3(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = null;

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $root->url(), $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A Build Dir is required');

        $object->file($name);
    }

    /** @throws RuntimeException */
    public function testFileWithManifest4(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent('');

        $dir2 = vfsStream::newDirectory('.vite');
        $dir2->addChild($file1);

        $dir1 = vfsStream::newDirectory($buildDir);
        $dir1->addChild($dir2);

        $root->addChild($dir1);

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $root->url(), $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not read Vite manifest at: %s', $file1->url()),
        );

        $object->file($name);
    }

    /** @throws RuntimeException */
    public function testFileWithManifest5(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent('{test:');

        $dir2 = vfsStream::newDirectory('.vite');
        $dir2->addChild($file1);

        $dir1 = vfsStream::newDirectory($buildDir);
        $dir1->addChild($dir2);

        $root->addChild($dir1);

        $serverUrl = new ServerUrlHelper();

        $object = new ViteUrl($serverUrl, $root->url(), $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not decode Vite manifest at: %s', $file1->url()),
        );

        $object->file($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithManifest6(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';
        $file     = 'test-xyz.js';
        $file2    = 'test-xyz2.js';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([$name => ['file' => $file]]));

        $dir2 = vfsStream::newDirectory('.vite');
        $dir2->addChild($file1);

        $dir1 = vfsStream::newDirectory($buildDir);
        $dir1->addChild($dir2);

        $root->addChild($dir1);

        $serverUrl = $this->createMock(ServerUrlHelper::class);
        $serverUrl->expects(self::once())
            ->method('generate')
            ->with('/' . $buildDir . '/' . $file)
            ->willReturn('/' . $buildDir . '/' . $file2);

        $object = new ViteUrl($serverUrl, $root->url(), $buildDir);

        self::assertSame('/' . $buildDir . '/' . $file2, $object->file($name));
    }
}
