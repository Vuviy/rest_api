<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enums\HttpStatus;
use App\Exception\NotFoundException;
use App\Response;

final class MigrationGuideController
{
    public function __construct(
        private string $docsPath,
    ) {
    }

    public function v1ToV2(): Response
    {
        return $this->renderGuide('migration-v1-to-v2.md');
    }

    /**
     * Read a markdown guide from the docs directory and serve it verbatim.
     * A missing or unreadable file is a 404 rather than a broken 200 body.
     */
    private function renderGuide(string $file): Response
    {
        $path = $this->docsPath . '/' . $file;

        $content = is_file($path) ? file_get_contents($path) : false;

        if ($content === false) {
            throw new NotFoundException('Migration guide not found');
        }

        return Response::raw(
            $content,
            HttpStatus::OK,
            ['Content-Type' => 'text/markdown; charset=utf-8'],
        );
    }
}
