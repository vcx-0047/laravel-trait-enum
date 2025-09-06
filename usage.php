Example::getEnum('status');
// Example: ['pending', 'active', 'closed']

Example::getEnums();
// [
//   'status' => ['pending', 'active', 'closed'],
//   'type'   => ['full_time', 'part_time']
// ]

Example::setEnum('status', 'off');
// [
//   'status' => ['pending', 'active', 'closed' ,'off'],
//   'type'   => ['full_time', 'part_time']
// ]
Example::removeEnum('status', 'active');
// [
//   'status' => ['pending', 'closed' ,'off'],
//   'type'   => ['full_time', 'part_time']
// ]
