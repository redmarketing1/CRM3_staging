
<h4>Hallo {{$content->name}},</h4>

<p style="margin-left: 110px">Sie sind zu folgenden Kostenvoranschlägen eingeladen:</p>
<span>Project: </span><a href="{{ route('projects.show',[$content->estimations[0]->project()->id]) }}">{{$content->estimations[0]->project()->name}}</a>
<ol>
@foreach($content->estimations as $estimation)
	<li>{{$estimation->title}}</li>
@endforeach
</ol>
