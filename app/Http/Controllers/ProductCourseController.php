<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductLesson;
use App\Models\ProductModule;
use App\OrderDetail;
use App\Services\ProductCourseService;
use App\Models\CourseStudent;
use App\UsersProduct;
use Illuminate\Http\Request;

class ProductCourseController extends Controller
{
    public function getCourseStudents(Request $request)
    {
        $product = UsersProduct::find($request->courseId);
        return (new ProductCourseService($product))->getPaginatedCourseStudent();
    }

    public function saveCourse($curriculum, $product)
    {
        $previousModuleIds = ProductModule::where('product_id', $product->id)->pluck('id')->toArray();
        $latestModuleIds = collect($curriculum)->pluck('id')->toArray();
        $removedModuleIds = array_values(array_diff($previousModuleIds, $latestModuleIds));
        ProductModule::whereIn('id', $removedModuleIds)->each(function ($module) {
            $module->delete();
            $module->lessons->each(function ($lesson) {
                $lesson->delete();
            });
        });

        foreach ($curriculum as $module) {
            $productModule = ProductModule::updateOrCreate(
                [
                    'id' => $module['id'],
                    'product_id' => $product->id,
                ],
                [
                    'title' => $module['name'],
                    'is_published' => $module['isPublished'],
                    'order' => $module['order'],
                ]
            );

            $previousLessonIds = ProductLesson::where('product_module_id', $productModule->id)->pluck('id')->toArray();
            $latestLessonIds = collect($module['elements'])->pluck('id')->toArray();
            $removedLessonIds = array_values(array_diff($previousLessonIds, $latestLessonIds));
            ProductLesson::whereIn('id', $removedLessonIds)->each(function ($lesson) {
                $lesson->delete();
            });

            foreach ($module['elements'] as $lesson) {

                ProductLesson::updateOrCreate(
                    [
                        'id' => $lesson['id'],
                        'product_module_id' => $productModule->id
                    ],
                    [
                        'title' => $lesson['name'],
                        'description' => $lesson['description'],
                        'video_url' => $lesson['videoUrl'],
                        'order' => $lesson['order'],
                        'parameter' => json_encode($lesson['parameter']),
                        'is_published' => $lesson['isPublished'],
                    ]
                );
            }
        }
    }

    public function removePeople(Request $request)
    {
        CourseStudent::find($request->query('csid'))->delete();
        $course = UsersProduct::find($request->courseId);
        return response()->json([
            'students' => (new ProductCourseService($course))->getPaginatedCourseStudent(),
        ]);
    }

    public function addStudent(Request $request)
    {
        $existingStudent = CourseStudent::withTrashed()->where('product_id', $request->courseId)->pluck('processed_contact_id')->toArray();
        $removedStudent = array_values(array_diff($existingStudent, $request->studentIds));
        $addedStudent = array_values(array_diff(array_merge($existingStudent, $request->studentIds), $removedStudent));
        foreach ($addedStudent as $id) {
            $courseStudent =  CourseStudent::withTrashed()->firstOrCreate(
                [
                    'product_id' => $request->courseId,
                    'processed_contact_id' => $id,
                ],
                [
                    'is_active' => 1,
                    'join_at'  => date('Y-m-d H:i:s'),
                ]
            );
            if ($courseStudent->trashed()) $courseStudent->restore();
        }

        CourseStudent::whereIn('processed_contact_id', $removedStudent)?->each(function ($student) {
            $student->delete();
        });

        $course = UsersProduct::find($request->courseId);
        return response()->json([
            'students' => (new ProductCourseService($course))->getPaginatedCourseStudent(),
        ]);
    }
}
