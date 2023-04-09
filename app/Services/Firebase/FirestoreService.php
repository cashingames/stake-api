<?php

namespace App\Services\Firebase;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreService
{
    private FirestoreClient $firestore;
    public function __construct()
    {
        $credentials = 'google-credentials.json';
        if (request()->header('x-request-env') == 'development' || env('GOOGLE_CREDENTIALS_ENV') == 'development') {
            $credentials = 'google-credentials-dev.json';
        }
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/firebase/' . $credentials));
        $this->firestore = app()->make(FirestoreClient::class);
    }

    public function createDocument(string $collection, string $document, array $data): void
    {
        $this->firestore->document($collection . '/' . $document)->set($data);
    }

    public function updateDocument(string $collection, string $document, array $data): void
    {
        $this->firestore->document($collection . '/' . $document)->set($data, ['merge' => true]);
    }

    public function getDocument(string $collection, string $document): array
    {
        return $this->firestore->document($collection . '/' . $document)->snapshot()->data();
    }

    public function deleteDocument(string $collection, string $document): void
    {
        $this->firestore->document($collection . '/' . $document)->delete();
    }

}
