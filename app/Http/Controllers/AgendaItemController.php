<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendaItemRequest;
use App\Models\AgendaItem;
use App\Models\Meeting;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AgendaItemController extends Controller
{
    public function store(AgendaItemRequest $request, Meeting $meeting): RedirectResponse
    {
        $data = $this->normalizeAssignee($request->validated());
        $parentId = $data['parent_id'] ?? null;

        $nextOrder = $parentId
            ? ((int) AgendaItem::where('parent_id', $parentId)->max('order')) + 1
            : ((int) $meeting->topLevelAgendaItems()->max('order')) + 1;

        $meeting->agendaItems()->create([...$data, 'order' => $nextOrder]);

        $status = $parentId ? '子項目を追加しました。' : '次第を追加しました。';

        return redirect()->route('meetings.edit', $meeting)->with('status', $status);
    }

    public function update(AgendaItemRequest $request, Meeting $meeting, AgendaItem $agendaItem): RedirectResponse
    {
        $this->ensureBelongsToMeeting($meeting, $agendaItem);

        $agendaItem->update($this->normalizeAssignee($request->validated()));

        return redirect()->route('meetings.edit', $meeting)->with('status', '次第を更新しました。');
    }

    /**
     * A registered member always takes precedence over a free-typed name,
     * so the two can't end up disagreeing about who's assigned.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeAssignee(array $data): array
    {
        if (! empty($data['member_id'])) {
            $data['assignee_name'] = null;
        }

        return $data;
    }

    public function destroy(Meeting $meeting, AgendaItem $agendaItem): RedirectResponse
    {
        $this->ensureBelongsToMeeting($meeting, $agendaItem);

        $agendaItem->delete();

        return redirect()->route('meetings.edit', $meeting)->with('status', '次第を削除しました。');
    }

    public function moveUp(Meeting $meeting, AgendaItem $agendaItem): RedirectResponse
    {
        $this->swapOrder($meeting, $agendaItem, 'up');

        return redirect()->route('meetings.edit', $meeting);
    }

    public function moveDown(Meeting $meeting, AgendaItem $agendaItem): RedirectResponse
    {
        $this->swapOrder($meeting, $agendaItem, 'down');

        return redirect()->route('meetings.edit', $meeting);
    }

    private function swapOrder(Meeting $meeting, AgendaItem $agendaItem, string $direction): void
    {
        $this->ensureBelongsToMeeting($meeting, $agendaItem);

        $items = $agendaItem->parent_id
            ? AgendaItem::where('parent_id', $agendaItem->parent_id)->orderBy('order')->get()
            : $meeting->topLevelAgendaItems()->get();
        $index = $items->search(fn (AgendaItem $item) => $item->id === $agendaItem->id);
        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (! $items->has($swapIndex)) {
            return;
        }

        $neighbor = $items->get($swapIndex);
        [$orderA, $orderB] = [$agendaItem->order, $neighbor->order];

        $agendaItem->update(['order' => $orderB]);
        $neighbor->update(['order' => $orderA]);
    }

    private function ensureBelongsToMeeting(Meeting $meeting, AgendaItem $agendaItem): void
    {
        if ($agendaItem->meeting_id !== $meeting->id) {
            throw new NotFoundHttpException;
        }
    }
}
