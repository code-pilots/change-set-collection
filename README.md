# ChangeSetCollection Util
The utility is designed to detect the state of change set relative to two iterables list

Example:
```php
$changeSet = new ChangeSetCollection(
    [
        new User(id: 1, name: 'User 1'),
        new User(id: 5, name: 'User 5'),
        new User(id: 7, name: 'User 7'),
    ],
    [
        new User(id: 1, name: 'User 1'),
        new User(id: 10, name: 'User 10'),
    ],
    fn (User $user) => $user->getId(),
);

echo $line = '--------' . PHP_EOL;
foreach ($changeSet as $change) {
    if ($change->isAdd()) {
        echo '+' . $change->updateData->getId() . PHP_EOL;
        echo $line;
    } elseif ($change->isEdit()) {
        echo '-' . $change->element->getId() . PHP_EOL;
        echo '+' . $change->updateData->getId() . PHP_EOL;
        echo $line;
    } elseif ($change->isRemove()) {
        echo '-' . $change->element->getId() . PHP_EOL;
        echo $line;
    }
}
```

Output:
```
--------
-1
+1
--------
+10
--------
-5
--------
-7
--------
```
