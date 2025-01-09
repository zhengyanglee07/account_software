<?php

namespace App\Http\Controllers;

use App\image_gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Traits\AuthAccountTrait;

use Image;

class ImagesController extends Controller
{
    use AuthAccountTrait;

    protected $storagePath;

    public function __construct() {
        $this->storagePath = storage_path() . "/app/public/media/";
    }

    /**
     * Get all images of current user account
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $currentAccountId = $this->getCurrentAccountId();
        $currentAccountImages = image_gallery::where('account_id', $currentAccountId)->latest()->get();

        return response()->json([
            'status' => 'success',
            'images' => $currentAccountImages
        ]);
    }

    /**
     * store user's uploaded images into storage disk and 
     * create record for user's uploaded images
     * 
     * @param \Illuminate\Http\Request $request
     * @author Darren Ter
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        $file = $request->file('image');
        $isFromAPI = $request->path() === 'api/v1/images/upload';

        if(!Storage::exists('/public/media')) {
            Storage::makeDirectory('/public/media');
        }

        //* get filename with extension
        $filenameWithExt = $file->getClientOriginalName();

        //* get filename only without extension
        $originalFilename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

        //* remove special characters and spaces
        $originalFilename = preg_replace("/[^A-Za-z0-9 ]/", '', $originalFilename);
        $originalFilename = preg_replace("/\s+/", '-', $originalFilename);

        //* get image extension
        $extension = $file->extension();

        //* refer to the method createWebpInstance()
        $filenameToStore = $this->createWebpInstance($file, $originalFilename, $extension, $isFromAPI);

        //* refer to the method resize()
        $result = $this->resize($filenameToStore, $extension, $isFromAPI);

        if(!$isFromAPI) {
            $newImage = image_gallery::create([
                'account_id' => Auth::user()->currentAccountId,
                'name' => $filenameWithExt,
                'size' => filesize($file) / 1024,
                'width' => getimagesize($file)[0],
                'height' => getimagesize($file)[1],
                's3_url' => $result->s3_url ?? null,
                's3_name' => $result->s3_name ?? null,
                's3_webp_name' => $result->s3_webp_name ?? null,
                's3_webp_url' => $result->s3_webp_url ?? null,
                's3_webp_size' => $result->s3_webp_size ?? 0,
                'local_path' => $result->local_path ?? null
            ]);
        } else {
            $newImage = $result->s3_url;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'File is successfully uploaded',
            'image' => $newImage,
        ]);
    }

    /**
     * Create webp version of image 
     * Store original image and webp image in storage
     *
     * @param binary $request->file
     * @param string $originalFilename
     * @param string $extension
     * @author Darren Ter
     * @return string $filenameToStore
     */
    public function createWebpInstance($file, $originalFilename, $extension, $isFromAPI = false)
    {
        $directory = $isFromAPI
            ? 'free-tool/media'
            : 'media';
        $formatArray = $extension === 'gif' 
            ? array($extension)
            : array($extension, 'webp');
        $filenameToStore = $originalFilename . '_' . time();

        foreach ($formatArray as $format) {

            if($extension !== 'gif') {
                $image = Image::make($file);
                
                $image->save(
                    //* temporarily store the images in local disk, to be used by resize() later
                    $this->storagePath . $filenameToStore . "." . $format, 85, $format
                );
            } else {
                $image = $file->storeAs('public/media', $filenameToStore . "." . $format);
            }

            Storage::disk('s3')->put(
                "{$directory}/{$filenameToStore}.{$format}", 
                file_get_contents(storage_path('/app/public/media/'. $filenameToStore . "." . $format)),
                'public'
            );
        }

        return $filenameToStore;
    }

    /**
     * Resize the webp instance of original image using the InterventionImage package
     * Save the images into disk
     * 
     * @param string $filenameToStore
     * @param string $originalExtension
     * @return object
     */
    private function resize($filenameToStore, $originalExtension, $isFromAPI = false)
    {
        $isLocal = app()->environment() === 'local';
        $imageHost = $isLocal
            ? config('filesystems.disks.s3.url')
            : config('filesystems.disks.s3.bucket');
        $directory = $isFromAPI
            ? 'free-tool/media'
            : 'media';
        $imageUrl = $isLocal 
            ? "https://" . $imageHost . '/' . config('filesystems.disks.s3.bucket') . "/{$directory}/{$filenameToStore}"
            : "https://" . $imageHost . "/{$directory}/{$filenameToStore}";
            
        $isGif = $originalExtension === 'gif';
        $originalImageName = "{$filenameToStore}.{$originalExtension}";
        $originalImagePath = "{$imageUrl}.{$originalExtension}";

        if(!$isGif) {
            $oriImage = file_get_contents("{$this->storagePath}/{$filenameToStore}.webp");
    
            $imageWidths = array(
                "small" => 480,
                "medium" => 960,
                "large" => 1440,
            );
    
            foreach($imageWidths as $key => $width) {
                $image = Image::make($oriImage);
                
                if($image->width() > $width) {
                    //* generate image for different screen size
                    $image = $image->resize($width, null, function($constraint) {
                        $constraint->aspectRatio();
                    });
                }

                $image->encode("webp", 85);
                
                $fileNameToStore = "{$filenameToStore}--{$key}.webp";

                Storage::disk('s3')->put(
                    "{$directory}/{$fileNameToStore}", $image->__toString(), 'public'
                );
            }
        }

        if($originalExtension !== 'webp' && !$isGif) {
            unlink("{$this->storagePath}{$filenameToStore}.webp");
        }

        unlink("{$this->storagePath}{$originalImageName}");

        return (Object) [
            "s3_name" => $originalImageName,
            "s3_url" => $originalImagePath,
            's3_webp_name' => $isGif ? $originalImageName : "{$filenameToStore}.webp",
            's3_webp_url' => $isGif ? $originalImagePath : "{$imageUrl}.webp",  
        ];
    }

    /**
     * delete image record and remove it from storage disk
     * 
     * @param image_gallery $image
     * @author Darren Ter
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(image_gallery $image)
    {
        try {
            $image->delete();
            if($image->local_path) {
                $imagePath = explode('/storage/media/', $image->local_path);
                unlink(storage_path('app/public/media/' . $imagePath[1]));
            } else {
                $imageNameOnly = explode(".webp", $image->s3_webp_name)[0];
                Storage::disk('s3')->delete([
                    "media/" . $image->s3_name,
                    "media/" . $image->s3_webp_name,
                    "media/" . $imageNameOnly . "--large.webp",
                    "media/" . $imageNameOnly . "--medium.webp",
                    "media/" . $imageNameOnly . "--small.webp"
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully deleted image'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to delete image'
            ], 500);
        }
    }
}
