create table issue16_bet (   id int auto_increment not null primary key,
     name varchar(255),
     portfolio_id varchar(255),
     portfolio int(11));

create table issue16_portfolio (    id int auto_increment not null primary key,
     name varchar(255),
     user_id varchar(255),
     user int(11));
alter table issue16_portfolio add parent_porfolio_id int;
alter table issue16_portfolio add (
     parent_portfolio_id varchar(255),
     parent_portfolio int(11)
);
alter table issue16_portfolio drop user_id
;
alter table issue16_portfolio drop user
;
alter table issue16_portfolio drop parent_porfolio_id
;
