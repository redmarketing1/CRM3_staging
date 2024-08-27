<option value="Taskly" {{(isset($_GET['account_type']) && $_GET['account_type'] == 'Taskly') ? 'selected' : '' }}>
    {{ __('Projects') }}
</option>
