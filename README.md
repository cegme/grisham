paper
=====

We are looking at ways of modeling  paper and user preferences.

Building CSV Files
====

Compile the code

	g++ -O3 -o parseDBLP  parseDBLP.cpp 

Run the code

	time ./parseDBLP <dirloc>/DBLPOnlyCitationOct19.txt ./

This will produce 3 files in the directory in the second parameter `author.csv`, 
`paper.csv` and `reference.csv`.



Example `author.csv`
=====

	pid|person
	0|José A. Blakeley
	1|Yuri Breitbart
	1|Hector Garcia-Molina
	1|Abraham Silberschatz
	2|Yuri Breitbart
	2|Tom C. Reyes


Example `paper.csv`
=====
	pid|papertitle|pubyear|venue|abstract
	0|OQL[C++]: Extending C++ with an Object Query Capability.|1995|Modern Database Systems|
	1|Transaction Management in Multidatabase Systems.|1995|Modern Database Systems|
	2|Overview of the ADDS System.|1995|Modern Database Systems|
	3|Multimedia Information Systems: Issues and Approaches.|1995|Modern Database Systems|
	4|Active Database Systems.|1995|Modern Database Systems|
	5|Where Object-Oriented DBMSs Should Do Better: A Critique Based on Early Experiences.|1995|Modern Database Systems|
	6|Distributed Databases.|1995|Modern Database Systems|
	7|An Object-Oriented DBMS War Story: Developing a Genome Mapping Database in C++.|1995|Modern Database Systems|


Example `reference.csv`
=====
	pid|citation
	4|995520
	14|1064647
	24|26682
	24|807142
	24|1065531
	24|855968
	25|165
	69|1118192


Postgres Copy Statments
====

	COPY author FROM '/<path>/author.csv' WITH (FORMAT csv, DELIMITER '|', HEADER True, ENCODING 'utf8');
	COPY paper FROM '/<path>/paper.csv' WITH (FORMAT csv, DELIMITER '|', HEADER True, ENCODING 'utf8');
	COPY reference FROM '/<path>/reference.csv' WITH (FORMAT csv, DELIMITER '|', HEADER True, ENCODING 'utf8');



Theta File
====

The theta file contains the documents and an array with the distribution. 
It should be formated as follows:

	0|{0.3,0.1,0.2,0.4}
	1|{0.5,0.1,0.2,0.2}
	2|{0.3,0.1,0.3,0.3}

This file can be copied to the DB using the `COPY' command.

	COPY theta FROM '/<path>/theta.csv' WITH (FORMAT csv, DELIMITER '|', HEADER False, ENCODING 'utf8');


Topic Word file
====

This file contans a list of the top words for each topic. Each word in the 
array format needs to be double quoted so that it can be red directly by 
postgres.

0|{''cat'', ''dog'', ''bird''}
1|{''fish'', ''shark'', ''dolphin''}
2|{''snake'', ''frog'', ''iguana''}


This file can be copied to the DB using the `COPY' command.

	COPY theta FROM '/<path>/topic_words' WITH (FORMAT csv, DELIMITER '|', HEADER True, ENCODING 'utf8');


Postgres Table Schemas
====

	CREATE TABLE author (
	 pid  integer,
	 person character varying
	);


	CREATE TABLE reference (
	 pid integer,
	 citation integer
	);


	CREATE TABLE paper (
	 id integer,
	 papertitle text,
	 pubyear integer,
	 venue text,
	 abstract text
	);

	CREATE TABLE theta (
		pid integer,
		topic_distribution double precision[]
	);

	CREATE TABLE topic_words (
		tid integer, 
		words varchar[],
	);




Ranking query : 
select comparetable.weight, comparetable.pid 
from (select sum(ln(1 - value.t - 0.0000000000001)) + ln(value.pi+0.0000000000001) - ln(1-value.pi-0.0000000000001) as weight, value.pid as pid 
       from (select unnest(tab.topic_distribution) as t, tab.pid as pid, tab.topic_distribution[1] as Pi
	from  (select pid, topic_distribution from theta LIMIT 20) as tab)
as value GROUP BY value.pid, value.pi) as comparetable ORDER BY comparetable.weight DESC;
