4xxi_test
=========

## Установка
Необходимо создать БД:
```
mkdir app/data
touch app/data/data.db3
app/console doctrine:database:create 
app/console doctrine:schema:update
```
Установить дополнительные пакеты:
```
composer install
```


## Затраченное время
- разворачивание и настройка - 2 ч
- реализация регистрации, авторизации, шаблонов - 4 ч
- Локализация - 2 ч
- Реализация сущностей, связей и БД - 4 ч
- Получение данных от **Yahoo Finance**, подготовка данных и реализация графика на **Google Charts** - 6 ч
- Прочие мелочи и доработки - 1 ч

## Дополнительно
В проекте используется **Guzzle** для общения с API **Yahoo Finance** и **FOSUserBundle** для реализации механизма работы с пользователями.

График стоимости портфеля рисуется на странице просмотра портфеля /portfolio/1. 

Поскольку не до конца понял как должен был выглядеть сам график, то отдельно вывел графики стоимости одной акции на момент закрытия и общая стоимость портфеля, как сумма всех стоимостей акций на текущий день. Под самим графиком есть фильтр, который позволяет скрывать отдельные акции для удобства.