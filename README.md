# interview  site
работу сайта можно посмотреть на странице: http://stark-cliffs-15914.herokuapp.com
## Запрос к базе данных, который позволяет получить все книги, у которых не менее двух авторов
### Запрос SQL
```
SELECT book.* 
    FROM book 
    RIGHT JOIN (SELECT book_id 
        FROM author_book 
        GROUP BY book_id 
        HAVING count(author_id) >= 2) t 
    ON book.id=t.book_id;
```
