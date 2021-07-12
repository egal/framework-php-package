# AWS File Storing

## Installation

1. Install `league/flysystem-aws-s3-v3` version `^1.0.0`.

   ```shell script
   composer require league/flysystem-aws-s3-v3:^1.0.0
   ```

2. Create if not exists `filesystems.php` file, register and configure
   `s3` disk.

   ```php
   return [

       // ...

       'disks' => [

           // ...

           's3' => [
               'driver' => 's3',
               'key' => env('AWS_ACCESS_KEY_ID'),
               'secret' => env('AWS_SECRET_ACCESS_KEY'),
               'region' => env('AWS_DEFAULT_REGION'),
               'bucket' => env('AWS_BUCKET'),
               'url' => env('AWS_URL'),
               'endpoint' => env('AWS_ENDPOINT'),
               'visibility' => env('AWS_VISIBILITY', 'private'),
           ],

           // ...

       ],

       // ...

   ];
   ```

3. Use trait `Egal\Model\Traits\AwsFileStoring` in your model.

   ```php
   namespace App\Models;

   use Egal\Model\Model;
   use Egal\Model\Traits\AwsFileStoring;

   /**
    * @property $id
    */
   class Document extends Model
   {

       use AwsFileStoring;

   }
   ```

4. Add your content names in model.

   ```php
   private array $contentNames = [
       'file'
   ];
   ```

5. Add fields to model focusing on content names.

   ```php
   /**
    * @property $id
    * @property string $file_path {@property-type field} {@validation-rules required|string}
    * @property string $file_url {@property-type fake-field}
    */
   ```

## Usage

### Upload

1. Call `Document/upload` action.

   Parameters:

   ```json
   {
       "file_basename":  "example.txt",
       "contents": "Contents of file"
   }
   ```

   In result should be `path`, collect this for save in model.

### Multipart upload

1. Call `Document/createMultipartUpload` action.

   Parameters:

   ```json
   {
       "file_basename":  "example.txt"
   }
   ```

   In result should be `path` and `upload_id`, collect this for next
   steps.

2. Split file contents to parts with length minimum is 5242880 symbols.
3. Call `Document/uploadPart` action for all parts of split file
   contents.

   Parameters:

   ```json
    {
        "upload_id": "upload_id from create multipart upload action result",
        "path": "path from create multipart upload action result",
        "part_number": "part number from 1 to 10000"
    }
   ```

4. Call `Document/completeMultipartUpload` action.

   Parameters:

   ```json
    {
        "upload_id": "upload_id from create multipart upload action result",
        "path": "path from create multipart upload action result"
    }
   ```

   In result should be `path`, collect this for save in model.

### Saving model

Create or update someone model with changing content path field.

Example:

Call `Document/create` action with parameters:

```json
{
    "attributes": {
        "file_path": "path from upload result"
    }
}
```

For get url of file just call get action, and collect content url filed.

Example:

Call `Document/getItem` action with parameters:

```json
{
    "id": 1
}
```

in result should be:

```json
{
    "id": 1,
    "file_url": "URL to your file",
    "updated_at": "2021-06-28T05:38:13.000000Z",
    "created_at": "2021-06-28T05:38:13.000000Z"
}
```

### Changing content fields postfixes

For change content path property name postfix - declare property in
model:

```php
private string $contentPathPropertyNamePostfix = '_path';
```

For change content url property name postfix - declare property in
model:

```php
private string $contentUrlPropertyNamePostfix = '_url';
```

### Disable url fields mutators

> Let's say you need store file URL in database, you must disable url
> fields mutators and realize storing.

Declare property in model:

```php
private bool $needMutateUrlFields = false;
```

