// BEGIN: ed8c6549bwf9
$response->assertStatus(200);

// Assert that the image was uploaded
Storage::disk('icons')->assertExists($file->hashName());

// Delete the uploaded image
Storage::disk('icons')->delete($file->hashName());
// END: ed8c6549bwf9