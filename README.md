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


Postgres Table Schemas
====



AUTHOR:
------

 Column |       Type        | Modifiers
--------+-------------------+-----------

 pid    | integer           |

 person | character varying |



REFERENCE:
---------

  Column  |  Type   | Modifiers
----------+---------+-----------
 pid      | integer |

 citation | integer |




PAPER:
-----

   Column   |  Type   | Modifiers
------------+---------+-----------
 id         | integer | 
 papertitle | text    | 
 pubyear    | integer | 
 venue      | text    | 
 abstract   | text    | 





