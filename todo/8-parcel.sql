-- Insert data into Parcel
INSERT INTO parcel (id, project_child_id, delimitation, tiffimage, title, start_date, planting_year, latitude, longitude, banner_image, created_at, created_by)
VALUES (1, 1, 'Delimitation 1', 'http://example.com/image1.tiff', 'Title 1', '2024-01-01 10:00:00', '2024-01-01 10:00:00', 0, 0, 'http://example.com/banner1.jpg', '2024-01-01 10:00:00', 'admin'),
       (2, 1, 'Delimitation 2', 'http://example.com/image2.tiff', 'Title 2', '2024-01-01 10:00:00', '2024-01-01 10:00:00', 0, 0, 'http://example.com/banner2.jpg', '2024-01-01 10:00:00', 'admin'),
       (3, 2, 'Delimitation 3', 'http://example.com/image3.tiff', 'Title 3', '2024-01-01 10:00:00', '2024-01-01 10:00:00', 0, 0, 'http://example.com/banner3.jpg', '2024-01-01 10:00:00', 'admin'),
       (4, 2, 'Delimitation 4', 'http://example.com/image4.tiff', 'Title 4', '2024-01-01 10:00:00', '2024-01-01 10:00:00', 0, 0, 'http://example.com/banner4.jpg', '2024-01-01 10:00:00', 'admin'),
       (5, 3, 'Delimitation 5', 'http://example.com/image5.tiff', 'Title 5', '2024-01-01 10:00:00', '2024-01-01 10:00:00', 0, 0, 'http://example.com/banner5.jpg', '2024-01-01 10:00:00', 'admin'),
       (6, 3, 'Delimitation 6', 'http://example.com/image6.tiff', 'Title 6', '2024-01-01 10:00:00', '2024-01-01 10:00:00', 0, 0, 'http://example.com/banner6.jpg', '2024-01-01 10:00:00', 'admin'),
       (7, 4, 'Delimitation 7', 'http://example.com/image7.tiff', 'Title 7', '2024-01-01 10:00:00', '2024-01-01 10:00:00', 0, 0, 'http://example.com/banner7.jpg', '2024-01-01 10:00:00', 'admin'),
       (8, 4, 'Delimitation 8', 'http://example.com/image8.tiff', 'Title 8', '2024-01-01 10:00:00', '2024-01-01 10:00:00', 0, 0, 'http://example.com/banner8.jpg', '2024-01-01 10:00:00', 'admin'),
       (9, 5, 'Delimitation 9', 'http://example.com/image9.tiff', 'Title 9', '2024-01-01 10:00:00', '2024-01-01 10:00:00', 0, 0, 'http://example.com/banner9.jpg', '2024-01-01 10:00:00', 'admin'),
       (10, 5, 'Delimitation 10', 'http://example.com/image10.tiff', 'Title 10', '2024-01-01 10:00:00', '2024-01-01 10:00:00', 0, 0, 'http://example.com/banner10.jpg', '2024-01-01 10:00:00', 'admin');
