FROM python:3.12-slim
RUN pip install --no-cache-dir google-auth==2.* requests==2.* "psycopg[binary]==3.*"
WORKDIR /app
COPY gsc_load.py /app/gsc_load.py
ENTRYPOINT ["python", "/app/gsc_load.py"]
