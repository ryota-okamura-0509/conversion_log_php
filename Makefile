.PHONY: conversion_log
conversion_log: 
	@docker compose run -e fileName=$(fileName) -e appName=$(appName) php_service php log.php ${fileName} ${appName}