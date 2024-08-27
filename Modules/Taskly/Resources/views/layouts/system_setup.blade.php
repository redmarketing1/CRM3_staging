
<div class="card sticky-top" style="top:30px">
    <div class="list-group list-group-flush" id="useradd-sidenav">
		<a href="{{route('pipelines.index')}}" class="list-group-item list-group-item-action border-0 {{ (request()->is('pipelines*') ? 'active' : '')}}">{{__('Pipelines')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>

		<a href="{{ route('labels.index') }}" class="list-group-item list-group-item-action border-0 {{ (request()->is('labels*') ? 'active' : '')}}">{{__('Label')}}<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>

        <a href="{{route('stages.index')}}" class="list-group-item list-group-item-action border-0 {{ (request()->is('stages*') ? 'active' : '')}}">{{__('Task Stage')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>

        <a href="{{route('bugstages.index')}}" class="list-group-item list-group-item-action border-0 {{ (request()->is('bugstages*') ? 'active' : '')}}">{{__('Bug Stage')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
    </div>
</div>
