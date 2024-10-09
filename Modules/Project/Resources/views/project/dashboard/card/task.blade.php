 <div class="card ">
     <div class="card-header">
         <div class="d-flex justify-content-between align-items-center">
             <div>
                 <h5 class="mb-0">
                     {{ __('Tasks On Going') }}
                 </h5>
             </div>
             <div class="float-end">
                 {{ $completeTasks }}
                 {{ __('Tasks completed out of') }}
                 {{ $totalTasks }}
             </div>
         </div>
     </div>
     <div class="card-body ">
         <div class="table-responsive">
             <table class="table table-centered table-hover mb-0 animated">
                 <tbody>
                     @forelse($tasks as $task)
                         <tr>
                             <td>
                                 <div class="font-14 my-1"><a
                                         href="{{ route('projects.task.board', [$task->project_id]) }}"
                                         class="text-body">{{ $task->title }}</a></div>

                                 @php($due_date = '<span class="text-' . ($task->due_date < date('Y-m-d') ? 'danger' : 'success') . '">' . date('Y-m-d', strtotime($task->due_date)) . '</span> ')

                                 <span class="text-muted font-13">{{ __('Due Date') }} :
                                     {!! $due_date !!}</span>
                             </td>
                             <td>
                                 <span class="text-muted font-13">{{ __('Status') }}</span> <br />
                                 @if ($task->complete == '1')
                                     <span class="badge bg-success p-2 px-3 rounded">{{ __($task->status) }}</span>
                                 @else
                                     <span class="badge bg-primary p-2 px-3 rounded">{{ __($task->status) }}</span>
                                 @endif
                             </td>
                             <td>
                                 <span class="text-muted font-13">{{ __('Project') }}</span>
                                 <div class="font-14 mt-1 font-weight-normal">{{ $task->project->name }}</div>
                             </td>
                             @if (Auth::user()->hasRole('client') || Auth::user()->hasRole('client'))
                                 <td>
                                     <span class="text-muted font-13">{{ __('Assigned to') }}</span>
                                     <div class="font-14 mt-1 font-weight-normal">
                                         @foreach ($task->users() as $user)
                                             <span
                                                 class="badge p-2 px-2 rounded bg-secondary">{{ isset($user->name) ? $user->name : '-' }}</span>
                                         @endforeach
                                     </div>
                                 </td>
                             @endif
                         </tr>
                     @empty
                         @include('layouts.nodatafound')
                     @endforelse
                 </tbody>
             </table>
         </div>
     </div>
 </div>
