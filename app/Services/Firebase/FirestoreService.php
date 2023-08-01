<?php

namespace App\Services\Firebase;

use Google\Cloud\Firestore\FirestoreClient;
use App\Traits\Utils\ResolveGoogleCredentials;

class FirestoreService
{
    use ResolveGoogleCredentials;
    private FirestoreClient $firestore;
    public function createDocument(string $collection, string $document, array $data, ?string $env = null): void
    {
        $this->resolveClient($env);

        $this->firestore->document($collection . '/' . $document)->set($data);
    }

    public function updateDocument(string $collection, string $document, array $data, ?string $env = null): void
    {
        $this->resolveClient($env);

        $this->firestore->document($collection . '/' . $document)->set($data, ['merge' => true]);
    }

    public function getDocument(string $collection, string $document, ?string $env = null): array
    {
        $this->resolveClient($env);
        return $this->firestore->document($collection . '/' . $document)->snapshot()->data();
    }

    public function deleteDocument(string $collection, string $document, ?string $env = null): void
    {
        $this->resolveClient($env);
        $this->firestore->document($collection . '/' . $document)->delete();
    }

    private function resolveClient(?string $env = null): void
    {
        $credentials = $this->getGoogleCredentialFileName($env);

        if (app()->environment('testing')) {
            putenv('GOOGLE_APPLICATION_CREDENTIALS='.storage_path($credentials) );
        } else {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/firebase/' . $credentials));
        }

        $this->firestore = app()->make(FirestoreClient::class);
    }
}
