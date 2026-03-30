<?php

namespace App\Services\Groups;

use App\Enums\DutyType;
use App\Models\Duty;
use App\Models\DutySlot;
use App\Models\Group;
use App\Notifications\DutyAssignedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CreateGroupDuty
{
    /**
     * @param  array{type: string, starts_on: string, member_ids: array<int>}  $attributes
     */
    public function handle(Group $group, array $attributes): Duty
    {
        return DB::transaction(function () use ($group, $attributes): Duty {
            /** @var Duty $duty */
            $duty = $group->duties()->create([
                'type' => $attributes['type'],
                'starts_on' => $attributes['starts_on'],
            ]);

            $memberIds = $attributes['member_ids'];

            foreach ($memberIds as $index => $userId) {
                $duty->members()->attach($userId, ['sort_order' => $index]);
            }

            $this->generateSlots($duty, $memberIds);

            $duty->load('members');

            foreach ($duty->members as $member) {
                $firstSlot = $duty->slots()->where('user_id', $member->id)->orderBy('date')->first();

                if ($firstSlot) {
                    $member->notify(new DutyAssignedNotification($duty, $firstSlot->date));
                }
            }

            return $duty;
        });
    }

    /**
     * @param  array<int>  $memberIds
     */
    private function generateSlots(Duty $duty, array $memberIds): void
    {
        $type = DutyType::from($duty->type instanceof DutyType ? $duty->type->value : $duty->type);
        $gap = $type->gapDays();
        $currentDate = Carbon::parse($duty->starts_on);
        $cycleCount = 4;

        $slots = [];

        for ($cycle = 0; $cycle < $cycleCount; $cycle++) {
            foreach ($memberIds as $userId) {
                $slots[] = [
                    'duty_id' => $duty->id,
                    'user_id' => $userId,
                    'date' => $currentDate->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $currentDate = $currentDate->copy()->addDays($gap['min']);
            }
        }

        DutySlot::query()->insert($slots);
    }
}
