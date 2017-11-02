<?php
//ru
return [
    'Return to list' => 'Вернуться к списку',
    'News #{id}'     => 'Новость N{id}',
    'You can update only your own post' => 'Ви можете редактировать только свои собственные новости',
    'You can update only your own post still unvisible'
                                        => 'Ви можете редактировать только свои собственные новости пока они не опубликованы',
    'The requested news does not exist' => 'Требуемой новости не существует',
    'Create' => 'Создать',
    'Save & view' => 'Сохранить и показать',
    'Record time (create/update)' => 'Время записи (создание/обновление)',
    'News invisible at frontend' => 'Новость не отображается на frontend',
] + include(__DIR__ . '/controller-main.php');// need to render front-views at backend
