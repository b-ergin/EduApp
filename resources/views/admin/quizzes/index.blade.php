@extends('admin.layout', ['title' => 'Quizzes'])

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; gap:12px; flex-wrap:wrap;">
        <h2 style="margin:0;">Quizzes</h2>
        <a class="btn" href="{{ route('admin.quizzes.create') }}">Add Quiz</a>
    </div>

    <p class="muted" style="margin-top:0; margin-bottom:14px;">
        Each grade has its own quiz path order. Drag rows inside a grade table, then click that grade's Save Order.
    </p>

    @forelse ($groupedQuizzes as $group)
        <section style="margin-bottom:18px; border:1px solid var(--border); border-radius:12px; padding:12px; background:#fff;">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:10px; flex-wrap:wrap;">
                <h3 style="margin:0;">{{ $group['grade_name'] }}</h3>
                <form method="POST" action="{{ route('admin.quizzes.reorder') }}" class="reorder-form" data-grade-id="{{ $group['grade_id'] }}">
                    @csrf
                    <input type="hidden" name="grade_id" value="{{ $group['grade_id'] }}">
                    <div class="order-inputs"></div>
                    <button class="btn" type="submit">Save Order</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width:60px;">Order</th>
                        <th style="width:70px;">Move</th>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Questions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="quiz-table-body" data-grade-id="{{ $group['grade_id'] }}">
                @foreach ($group['quizzes'] as $quiz)
                    <tr data-quiz-id="{{ $quiz->id }}" draggable="true" style="cursor: grab;">
                        <td><strong>{{ $quiz->sort_order ?? $loop->iteration }}</strong></td>
                        <td title="Drag to reorder">↕</td>
                        <td>{{ $quiz->title }}</td>
                        <td>{{ $quiz->subject?->name }}</td>
                        <td>{{ $quiz->questions_count }}</td>
                        <td class="actions">
                            <a class="btn" href="{{ route('admin.questions.index', ['quiz_id' => $quiz->id]) }}">Questions</a>
                            <a class="btn" href="{{ route('admin.quizzes.edit', $quiz) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}" onsubmit="return confirm('Delete this quiz and all related questions?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    @empty
        <p>No quizzes yet.</p>
    @endforelse

    <script>
        (function () {
            const tableBodies = Array.from(document.querySelectorAll('.quiz-table-body'));

            const refreshOrderColumns = (tbody) => {
                const rows = Array.from(tbody.querySelectorAll('tr[data-quiz-id]'));
                rows.forEach((row, index) => {
                    const orderCell = row.querySelector('td');
                    if (orderCell) {
                        orderCell.innerHTML = `<strong>${index + 1}</strong>`;
                    }
                });
            };

            const refreshHiddenInputs = (tbody) => {
                const gradeId = tbody.dataset.gradeId;
                const form = document.querySelector(`.reorder-form[data-grade-id="${gradeId}"]`);
                if (!form) return;

                const inputsContainer = form.querySelector('.order-inputs');
                if (!inputsContainer) return;

                inputsContainer.innerHTML = '';
                const rows = Array.from(tbody.querySelectorAll('tr[data-quiz-id]'));
                rows.forEach((row) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'order[]';
                    input.value = row.dataset.quizId;
                    inputsContainer.appendChild(input);
                });
            };

            const getDragAfterElement = (container, y) => {
                const rows = [...container.querySelectorAll('tr[data-quiz-id]:not(.dragging)')];
                return rows.reduce((closest, child) => {
                    const box = child.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;
                    if (offset < 0 && offset > closest.offset) {
                        return { offset, element: child };
                    }
                    return closest;
                }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
            };

            tableBodies.forEach((tbody) => {
                let draggingRow = null;

                tbody.querySelectorAll('tr[data-quiz-id]').forEach((row) => {
                    row.addEventListener('dragstart', () => {
                        draggingRow = row;
                        row.classList.add('dragging');
                    });

                    row.addEventListener('dragend', () => {
                        row.classList.remove('dragging');
                        draggingRow = null;
                        refreshOrderColumns(tbody);
                        refreshHiddenInputs(tbody);
                    });
                });

                tbody.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    if (!draggingRow) return;
                    const afterElement = getDragAfterElement(tbody, event.clientY);
                    if (afterElement == null) {
                        tbody.appendChild(draggingRow);
                    } else {
                        tbody.insertBefore(draggingRow, afterElement);
                    }
                });

                refreshOrderColumns(tbody);
                refreshHiddenInputs(tbody);
            });
        })();
    </script>
@endsection
