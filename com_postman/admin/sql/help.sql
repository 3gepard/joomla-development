DROP TABLE `xlefz_postman_log`, 
`xlefz_postman_log_bak`, 
`xlefz_postman_log_bak_old`, 
`xlefz_postman_newsgroups`, 
`xlefz_postman_newsgroups_bak`, 
`xlefz_postman_newsgroups_bak_old`, 
`xlefz_postman_newsgroups_subscribers`, 
`xlefz_postman_newsgroups_subscribers_bak`, 
`xlefz_postman_newsgroups_subscribers_bak_old`, 
`xlefz_postman_newsletters`, 
`xlefz_postman_newsletters_bak`, 
`xlefz_postman_newsletters_bak_old`, 
`xlefz_postman_subscribers`, 
`xlefz_postman_subscribers_bak`, 
`xlefz_postman_subscribers_bak_old`, 
`xlefz_postman_tickets`,
`xlefz_postman_tickets_bak`;

UPDATE `xlefz_postman_subscribers` SET active = 1

UPDATE  `xlefz_modules` 
SET 
	position = "footer-right", 
	published = 1
 WHERE title = 'Postman'