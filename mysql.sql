SELECT
  c.Name,
  (
    CASE 
        WHEN c.Email LIKE '%@mail.ru' THEN 0
        ELSE count(o.ID)
    END) AS cnt
FROM
  Orders o
LEFT JOIN Clients c ON c.Id = o.Clients_id
LEFT JOIN Products p ON p.Order_id = o.Id
WHERE
  o.Ctime BETWEEN UNIX_TIMESTAMP('2015-03-01 00:00:00') AND UNIX_TIMESTAMP('2015-04-01 00:00:00')
  AND p.Id IN (151515,151617,151514)
GROUP by c.Name
ORDER BY cnt DESC;