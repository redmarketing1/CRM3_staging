<fieldset @if ($estimation->status == 2) disabled @endif>
    <div class="table-responsive pt-5">
        <table class="table w-100 table-hover" id="estimation-edit-table">
            @include('estimation::estimation.show.table.thead')
            @include('estimation::estimation.show.table.tbody')
            {{-- @include('estimation::estimation.show.table.tfoot') --}}
        </table>
    </div>
</fieldset>
