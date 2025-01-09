<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\CourseStudent;
use App\Traits\AuthAccountTrait;
use Illuminate\Http\Request;

class EcommerceCourseController extends Controller
{
    use AuthAccountTrait;


    public function getCourses(Request $request)
    {
        $processedContact = $request->user()->processedContact;
        return response()->json([
            'courses' => $this->getFormattedCourse($processedContact)
        ]);
    }

    public function getCoursesDetail(Request $request)
    {
        $processedContact = $request->user()->processedContact;
        return response()->json([
            'course' => $this->getFormattedCourse($processedContact, [$request->referenceKey])->first()
        ]);
    }

    public function updateLessonProgress(Request $request)
    {
        $data[$request->query('lid')] = [
            'progress' => $request->query('progress'),
            'total_watched_duration' => $request->query('duration'),
        ];
        $request->user()->processedContact->lessonLog()->syncWithoutDetaching($data);

        if ($request->query('progress') === '100') {
            $student =  CourseStudent::find($request->query('csid'));
            $completedLessons = json_decode($student->completed_lesson ?? '[]', true);
            $student->update([
                'completed_lesson' => json_encode(array_unique([...$completedLessons, $request->query('lid')])),
                'last_access_at' =>  date('Y-m-d H:i:s')
            ]);
        }
        return response()->json($data);
    }

    private function getFormattedCourse($processedContact, $referenceKeys = null)
    {
        return CourseStudent::whereHas('products', function ($query) use ($referenceKeys) {
            if (!is_null($referenceKeys)) $query->whereIn('reference_key', $referenceKeys);
        })->where(
            'processed_contact_id',
            $processedContact->id
        )->get()->map(function ($student) {
            $course = $student->products->load(['modules.lessons.processedContact']);
            $course->studentId = $student->id;
            $course->modules = $course->modules->map(function ($module) use ($student) {
                $module->lessons = $module->lessons->map(function ($lesson) use ($student) {
                    $lessonLog = $lesson->processedContact?->find($student->processed_contact_id);
                    $lesson->progress = $lessonLog->pivot->progress ?? 0;
                    $lesson->totalWatchedDuration = $lessonLog->pivot->total_watched_duration ?? 0;
                    return $lesson;
                });
                return $module;
            });

            $course->totalLesson = $course->modules->flatMap(function ($module) {
                return $module->lessons;
            })->count();
            $course->totalCompleted = count(json_decode($student->completed_lesson ?? '[]', true));
            $course->isFulfilled = $student->is_active;
            if ($course->totalCompleted === 0) $course->status = 'pending';
            else if ($course->totalLesson === $course->totalCompleted) $course->status = 'finished';
            else $course->status = 'ongoing';
            return $course;
        });
    }
}
