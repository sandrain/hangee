--
-- schema.sql
--
-- Database scheme of the HanGEE web program.
--
-- Written by HyoGi Sim <sandrain@gmail.com>
--

drop table if exists hangee_users;
create table hangee_users (
	id	integer		not null auto_increment,
	email	char(128)	not null unique,
	name	char(20)	not null default '',
	password char(32)	not null,
	aip	char(15)	not null default '',
	atime	integer		not null default 0,
	grade	char(1)		not null default '9',
	testcrc	integer unsigned not null default 0,
	testpos integer		not null default 0,
	primary key (id)
);

insert into hangee_users (email, name, password, grade)
	values ('sandrain@gmail.com', 'sandrain', '55dfd6560240e1681406e8f1a98f1f8a', 0);

drop table if exists hangee_words;
create table hangee_words (
	id	smallint	not null auto_increment,
	page	smallint	not null,
	word	char(32)	not null unique,
	sense	varchar(512)	not null default '',
	hint	varchar(512)	not null default '',
	primary key (id)
);

drop table if exists hangee_marked;
create table hangee_marked (
	id	integer		not null auto_increment,
	uid	integer		not null references hangee_users(id),
	wid	smallint	not null references hangee_words(id),
	count	tinyint		not null default 0,
	primary key (id)
);

drop table if exists hangee_example;
create table hangee_example (
	id	integer		not null auto_increment,
	uid	integer		not null references hnagee_users(id),
	wid	smallint	not null references hangee_words(id),
	example	varchar(512)	not null default '',
	primary key (id),
	unique key (uid, wid)
);

drop table if exists hangee_hints;
create table hangee_hints (
	id	integer		not null auto_increment,
	uid	integer		not null references hnagee_users(id),
	wid	smallint	not null references hangee_words(id),
	hint	varchar(512)	not null default '',
	primary key (id),
	unique key (uid, wid)
);

drop table if exists hangee_test;
create table hangee_test (
	id	integer		not null auto_increment,
	uid	integer		not null references hangee_users(id),
	wid	smallint	not null references hangee_words(id),
	seq	integer		not null default 0,
	testcrc	integer unsigned not null default 0,
	primary key (id)
);

