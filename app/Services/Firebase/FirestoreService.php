<?php

namespace App\Services\Firebase;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreService
{
    public function __construct(
        private readonly FirestoreClient $firestore
    ) {
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
