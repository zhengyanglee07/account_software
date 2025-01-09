<?php

namespace App\Services;

use App\Models\CourseStudent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProductCourseService
{
    protected $contactId;
    public function __construct(protected $course = null, protected $contact = null)
    {
        $this->contactId =  Auth::guard('ecommerceUsers')->user()?->processedContact?->id ?? null;
    }

    public function getAllCourseStudent()
    {
        $constraints = [];
        if (isset($this->course->id)) $constraints['product_id'] = $this->course->id;
        if (isset($this->contact->id)) $constraints['processed_contact_id'] = $this->contact->id;

        return CourseStudent::with([
            'products.modules.lessons',
            'processedContact'
        ])->where($constraints);
    }

    public function getAllStudentContactIds()
    {
        $constraints = [];
        if (isset($this->course->id)) $constraints['product_id'] = $this->course->id;
        if (isset($this->contact->id)) $constraints['processed_contact_id'] = $this->contact->id;
        return CourseStudent::where($constraints)->pluck('processed_contact_id')->toArray();
    }

    public function getPaginatedCourseStudent()
    {
        $courseStudent = $this->getAllCourseStudent();

        $studentReformatFunc = function ($student) {
            $contact = $student->processedContact;
            $student->name = $contact->displayName;
            $student->email = $contact->email;
            $student->totalLesson = count($student->products->modules->flatMap(function ($module) {
                return $module->lessons;
            }));
            $student->totalCompleted = count(json_decode($student->completed_lesson ?? '[]', true));
            return $student;
        };
        return (new PaginatorService())->getPaginatedData($courseStudent, $studentReformatFunc, '/course/students/' . $this->course?->id);
    }

    public function checkIsPurchased()
    {
        if ($this->course->type !== 'course') return false;
        return CourseStudent::where(
            ['product_id' => $this->course->id, 'processed_contact_id' => $this->contactId]
        )->exists();
    }

    public function checkIsEnrolled()
    {
        if ($this->course->type !== 'course') return false;
        $courseStudent = CourseStudent::firstWhere(
            ['product_id' => $this->course->id, 'processed_contact_id' => $this->contactId]
        );
        if (!isset($courseStudent)) return false;
        if (empty($this->course->access_period)) return true;
        $accessPeriod = json_decode(($this->course->access_period), true);
        if (empty($accessPeriod['duration'])) return true;
        if ($accessPeriod['duration'] === 'lifetime') return true;
        $joinDate = Carbon::parse($courseStudent->join_at);
        $durationMap = [
            'Days' => 'addDay',
            'Weeks' => 'addWeek',
            'Months' => 'addMonth',
            'Years' => 'addYear',
        ];
        $expireDate = Carbon::parse($courseStudent->join_at)->{$durationMap[$accessPeriod['duration']]}($accessPeriod['period']);
        return $joinDate <= $expireDate;
    }
}
