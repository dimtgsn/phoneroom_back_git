<?php

namespace App\Notifications;
use App\Models\Order;
use App\Models\User;
use App\Utilities\DateFormatting;
use Illuminate\Bus\Queueable;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramFile;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Notifications\Notification;

class Telegram extends Notification
{
    use Queueable;

    public function __construct($order, $file)
    {
        // TODO рвзобраться с телеграмом
        $this->newOrder = $order;
        $this->file = $file;
    }

    public function via($notifiable)
    {
        return ["telegram"];
    }

    public function toTelegram($notifiable)
    {
        $url_choice_delivery = url('admin/orders/'.$this->newOrder->id.'/chose_delivery');
        $user = User::with('profile')->where('id', $this->newOrder->user_id)->first();

//        return TelegramMessage::create()
            // Optional recipient user id.
//            ->to($notifiable->telegram_user_id)
            // Markdown supported.
//            ->content("Привет!\n")
//            ->line("Я бот с уведомлениями.")
//            ->line("Я буду присылать *новые заказы* и всю информацию по ним.");

//            ->content("*Новый заказ*\n")
//            ->line("*Информация по заказу:*\n")
//            ->line("---------------------------------")
//            ->line("*Пользователь:*")
//            ->line("---------------------------------")
//            ->line($user->first_name)
//            ->line(($user->profile->middle_name ?? '').($user->profile->last_name ?? ''))
//            ->line("---------------------------------")
//            ->line("*Заказ:*")
//            ->line("---------------------------------")
//            ->line('Дата создания заказа - '.DateFormatting::format($this->newOrder->created_at))
//            ->line('Статус - '.$this->newOrder->status->name)
//            ->line('Общая сумма заказа - '.$this->newOrder->total.' ₽')
//            ->line('Адрес доставки - '.$this->newOrder->ship_address.', '.$this->newOrder->zip),
            return TelegramFile::create()
                ->content(
                "*Новый заказ*\n".
                        "---------------------------------\n".
                        "*Информация по заказу:*\n".
                        "---------------------------------\n".
                        "*Пользователь:*\n".
                        $user->first_name.' '.($user->profile->middle_name ?? '').($user->profile->last_name ?? '')."\n".
                        "Номер телефона - +".$user->phone.
                        "---------------------------------\n".
                        "*Заказ:*\n".
                        "*Дата создания заказа* - ".DateFormatting::format($this->newOrder->created_at)."\n".
                        "*Статус* - ".$this->newOrder->status->name."\n".
                        "*Общая сумма заказа* - ".$this->newOrder->total." ₽"."\n".
                        "*Адрес доставки* - ".$this->newOrder->ship_address.", ".$this->newOrder->zip
                )
                ->document($this->file, 'заказ.pdf')
                ->button('Выбрать доставку', 'https://google.com');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
