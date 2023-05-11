# ChangeSetCollection Util

The utility is designed to detect the state of change set relative to two iterables list

## Installation

Install the latest version with

```bash
$ composer require code-pilots/change-set-collection
```

## Basic Usage

### Example 1:
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
        echo '+' . $change->element2->getName() . PHP_EOL;
        echo $line;
    } elseif ($change->isEdit()) {
        echo '-' . $change->element1->getName() . PHP_EOL;
        echo '+' . $change->element2->getName() . PHP_EOL;
        echo $line;
    } elseif ($change->isRemove()) {
        echo '-' . $change->element1->getName() . PHP_EOL;
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

### Example 2:
```php
$changeSet = new ChangeSetCollection(
    [
        new Order(id: Uuid::from('00000000-0000-0000-0000-000000000001'), customer: 'Jon'),
        new Order(id: Uuid::from('00000000-0000-0000-0000-000000000002'), customer: 'Doe'),
    ],
    [
        ['uuid' => '00000000-0000-0000-0000-000000000001', 'customer' => 'Dan'],
        ['uuid' => '00000000-0000-0000-0000-000000000003', 'customer' => 'Wendy'],
    ],
    fn (User|array $order) => $order instanceof Order ? $order->getId() : $order['uuid'],
);

echo $line = '--------' . PHP_EOL;
foreach ($changeSet as $change) {
    if ($change->isAdd()) {
        echo '+' . $change->element2['customer'] . PHP_EOL;
        echo $line;
    } elseif ($change->isEdit()) {
        echo '-' . $change->element1->getCustomer() . PHP_EOL;
        echo '+' . $change->element2['customer'] . PHP_EOL;
        echo $line;
    } elseif ($change->isRemove()) {
        echo '-' . $change->element1->getCustomer() . PHP_EOL;
        echo $line;
    }
}
```

Output:
```
--------
-Jon
+Dan
--------
-Doe
--------
+Wendy
--------
```

## For contributors

### Run tests
Exec: `./vendor/bin/phpunit`

### Run lint
Exec phpstan: `./vendor/bin/phpstan analyse src tests`
Exec psalm: `./vendor/bin/psalm`
