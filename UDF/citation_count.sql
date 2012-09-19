CREATE OR REPLACE FUNCTION citation_score() RETURNS TABLE(pid integer, score float, level integer) AS $$
 DECLARE
  iterlevel integer;
  maxlevel integer := 5;
  maxpapers integer := 6;
 BEGIN
 
 -- Creating a temporary table that maps all the inlinks to each pid (that has one)
 CREATE TEMPORARY TABLE T AS SELECT r.pid, count(*)::float AS score, 1 as level
 FROM ref r
 GROUP BY r.pid
 ORDER BY r.pid;

	INSERT INTO T
	SELECT g, 0.0 as score, 1 as level
	FROM generate_series(1,maxpapers) g
	where g NOT IN (select distinct t.pid from T t);

  FOR iterlevel IN 2..maxlevel LOOP
    -- Insert into the table the value to be summed at each level
    INSERT INTO T
    SELECT z.pid, sum(z.score), max(z.level) as level
    FROM ((SELECT r.pid, GREATEST(0, sum((SELECT t.score::float FROM T t WHERE t.level = iterlevel-1 AND t.pid = r.citation)/iterlevel*1.0)) AS score, iterlevel as level
          FROM ref r
          GROUP BY r.pid) union all (select * from T) ) z
    WHERE z.level > iterlevel - 2
    GROUP BY z.pid;

    -- For each paper id in ref add up the score from the previous level divided by the current level 
    --(SELECT r.pid, GREATEST(0, sum((SELECT t.score::float FROM T t WHERE t.level = iterlevel-1 AND t.pid = r.citation)/iterlevel*1.0)) AS score, iterlevel
    -- FROM ref r
    -- GROUP BY r.pid);


  END LOOP;
  
 -- First return all the rows inside of T table for debugging purposes only
 RETURN QUERY SELECT t.pid, t.score, t.level FROM T t ;--WHERE t.level = maxlevel;
 -- Append the rolled up results to the bottom of the table (this is actually what we will return)
 -- RETURN QUERY SELECT t.pid, sum(t.score), max(t.level) FROM T t GROUP BY t.pid ;--WHERE t.level = maxlevel;
 DROP TABLE T;
 
 END;
 $$ LANGUAGE plpgsql;
