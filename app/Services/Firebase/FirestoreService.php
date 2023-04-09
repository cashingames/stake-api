<?php

namespace App\Services\Firebase;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreService
{
    private FirestoreClient $firestore;
    public function __construct(string $env = "")
    {
        if (request()->header('x-request-env') == 'development' || $env == 'development') {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/firebase/google-credentials-dev.json'));
            // $this->firestore = new FirestoreClient([
            //     'credentials' => storage_path('app/firebase/google-credentials-dev.json'),
            // ]);
        } else {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/firebase/google-credentials.json'));
            // $this->firestore = new FirestoreClient([
            //     'credentials' => storage_path('app/firebase/google-credentials.json'),
            // ]);
        }

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
