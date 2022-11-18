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
        new User(id: 1, name: 'EditedUser 1'),
        new User(id: 10, name: 'NewUser 10'),
    ],
    fn (User $user) => $user->getId(),
);

echo $line = '--------' . PHP_EOL;
foreach ($changeSet as $change) {
    if ($change->isAdd()) {
        echo '+' . $change->updateData->getName() . PHP_EOL;
        echo $line;
    } elseif ($change->isEdit()) {
        echo '-' . $change->element->getName() . PHP_EOL;
        echo '+' . $change->updateData->getName() . PHP_EOL;
        echo $line;
    } elseif ($change->isRemove()) {
        echo '-' . $change->element->getName() . PHP_EOL;
        echo $line;
    }
}
```

Output:
```
--------
-User 1
+EditedUser 1
--------
+NewUser 10
--------
-User 5
--------
-User 7
--------
```

### Run tests
Exec: `./vendor/bin/phpunit`

### Run lint
Exec phpstan: `./vendor/bin/phpstan analyse src tests`
Exec psalm: `./vendor/bin/psalm`
