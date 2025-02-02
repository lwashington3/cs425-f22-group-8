DROP FUNCTION IF EXISTS statement;
DROP FUNCTION IF EXISTS pending_transactions;

CREATE OR REPLACE FUNCTION statement(account UUID, month INT, year INT)
    RETURNS TABLE(day timestamp,
                  transaction_amount DOUBLE PRECISION,
                  account_balance DOUBLE PRECISION,
                  transaction_description TEXT
                 ) AS $$
DECLARE
BEGIN
    RETURN QUERY SELECT s.date, s.amount, s.running_total AS running_total, s.description FROM
                (SELECT date, description, amount, SUM(amount) OVER (ORDER BY date ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS running_total
                 FROM transactions
                 WHERE account_number = account) s
                WHERE EXTRACT(YEAR FROM date) = year AND EXTRACT(MONTH FROM date) = month
                 ORDER BY s.date;
END
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION pending_transactions(account UUID)
    RETURNS TABLE(day timestamp,
                  transaction_amount DOUBLE PRECISION,
                  account_balance DOUBLE PRECISION,
                  transaction_description TEXT
                 ) AS $$
DECLARE
BEGIN
    RETURN QUERY SELECT * FROM statement(account, EXTRACT(MONTH FROM now())::INT, EXTRACT(YEAR FROM now())::INT);
END
$$ LANGUAGE plpgsql;


-- SELECT * FROM pending_transactions('c61f5951-c69e-47c4-8fb6-ac13f800a4a4');
-- SELECT * FROM statement('c61f5951-c69e-47c4-8fb6-ac13f800a4a4', 12, 2022);